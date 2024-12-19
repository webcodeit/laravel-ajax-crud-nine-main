<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Models\SupportingAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Operation;
use App\Models\Offer;
use App\Models\UserLevel;

class UserRepository extends Repository {

    public function getAll($param, $pagination = true)
    {
        return User::with('city:id,name', 'issuer:id,ruc_text_id,ruc_code_optional', 'companies')->withTrashed()
        ->select(
            'users.id', 'users.slug', 'users.name', 'users.first_name', 'users.last_name', 'users.email', 'users.phone_number', 'users.security_level',
            'users.address', 'users.city_id', 'users.state','users.country_id','users.account_type','users.gender', 'users.is_active', 'users.deleted_at',
            'users.address_verify', 'users.address_verify_at', 'users.is_registered', 'users.address_verify_otp', 'users.registered_at', 'users.created_at',
            'users.last_login_ip', 'users.last_login_at','users.parent_id', 'users.issuer_id'
        )
        ->when($param['search'] ?? false, function ($query) use ($param) {
            $query->where(function ($qry) use ($param) {
                $qry->where('users.name', 'like', '%' . $param['search'] . '%')
                    ->orWhere('users.phone_number', 'like', '%' . $param['search'] . '%')
                    ->orWhere('users.first_name', 'like', '%' . $param['search'] . '%')
                    ->orWhere('users.last_name', 'like', '%' . $param['search'] . '%')
                    ->orWhere('users.email', 'like', '%' . $param['search'] . '%')
                    ->orWhere('users.address', 'like', '%' . $param['search'] . '%')
                    ->orWhere('users.account_type', 'like', '%' . $param['search'] . '%');
            })->orWhere(function ($query) use ($param) {
                $query->whereHas('issuer', function ($qry) use($param) {
                    $qry->where('ruc_text_id', 'like', '%' . $param['search'] . '%');
                });
            });
        })
        ->when(isset($param), function ($qry) use ($param) {
            if(isset($param['user_type']) && $param['user_type'] == 'admin') {
                $qry->where('users.is_admin', '1');
            } else if(isset($param['user_type']) && $param['user_type'] == 'user') {    
                $qry->where('users.is_admin', '!=','1')->where('users.is_user_company', '!=', '1');
            } else if(isset($param['user_type']) && $param['user_type'] == 'companies') {    
                $qry->where('users.is_admin', '!=','1')->where('users.is_user_company', '1');
            }

            if(isset($param['sort_type']) && isset($param['sort_column'])) {
                $qry->orderBy($param['sort_column'], $param['sort_type']);
            } else {
                $qry->orderBy('id', 'DESC');
            }
        })
        ->when($pagination, function ($query) use($param) {
            if(isset($param['per_page']) && $param['per_page']!=''){
                return $query->paginate($param['per_page']);
            } else {
                return $query->paginate(config('constants.PER_PAGE_ADMIN'));
            }
        }, function ($query) {
            return $query->get();
        });
    }

    public function findByIdOrSlug($id){
        return User::where('id', $id)->orWhere('slug', $id)->first();
    }

    public function firstWhereNotification($user_id){
        return User::where('id', $user_id)->select('id', 'name', 'email', 'user_level')->first();
    }

    public function findByIdOrSlugUserDetail($id){
        return User::where('id', $id)->orWhere('slug', $id)
        ->with([
            'city:id,name',
            'issuer:id,company_name,slug,ruc_text_id,ruc_code_optional,registry_in_mipo',
            'bank_details_all',
            'bank_details_all.issuer_bank',
            'id_proof_documents',
            'user_profile_attache'
        ])->withAvg('ratings', 'rating_number')->withCount('ratings')
        ->first();
    }

    public function userByOperation($param)
    {
        return  Operation::OperationSelect()->where('seller_id', $param['user_id'])
        // ->whereIn('operations_status', $param['op_status'])
        ->with([
            'seller' => function($qry){
                $qry->select('id', 'name', 'ruc_tax_id');
            },
            'issuer' => function($qry){
                $qry->select('id', 'company_name','ruc_text_id', 'ruc_code_optional');
            },
        ])
        ->get();
    }

    public function userSoldAndBuyByOperation($param)
    {
        $param = $param[0]?? $param ;
        return Offer::
            with(['buyer'  => function($query) use ($param) {
                $query->select('id', 'name');
            },
            'operations' => function($query) use ($param){
                $query->select('seller_id', 'issuer_id', 'operation_number','operation_type', 'slug', 'amount', 'operations_status', 'preferred_currency', 'preferred_payment_method', 'issuance_date', 'expiration_date', 'extra_expiration_days');
                $query->with([
                    'issuer' => function($qry){
                        $qry->select('id', 'company_name','ruc_text_id', 'ruc_code_optional');
                    }
                ]);
            }
            ])
            ->when($param, function($query) use ($param) {
                if($param['user_type'] == 'buyer') {
                    $query->where('buyer_id', '=',  $param['user_id']);
                }
                if($param['user_type'] == 'seller') {
                    $query->where('buyer_id', '!=',  $param['user_id']);
                    $query->whereHas('operations' , function($qry) use ($param){
                        $qry->where('seller_id', $param['user_id']);
                    });
                }
            })
            ->whereIn('offer_status', ['Approved', 'Completed'])
            ->get();
    }
    
}
?>
