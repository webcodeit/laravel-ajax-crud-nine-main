<?php 

namespace App\Repositories;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\OperationsLogs;
use App\Models\User;
use App\Models\Rating;
use App\Models\Offer;
use App\Models\UserLevel;
use App\Models\Issuer;
use App\Models\Operation;
use Carbon\Carbon;

class ExploreRepository extends Repository {

    public function getExploreOperationsGroup($param, $pagination = true)
    {
        return Operation::OperationSelect()
        ->with([
            'offers',
            'seller' => fn($qry) => $qry->select('id','name'), 
            'issuer' => fn($qry) => $qry->select('id','company_name')
            ])
            ->whereDoesntHave('offers', function($qry){
                $qry->where('buyer_id', $this->user_login_id);
            })
            ->where('seller_id', '!=', $this->user_login_id)
            ->where('operations_status', 'Approved')
            ->when($param['preferred_currency'], function ($qry) use($param) {
                // if(isset($param['preferred_currency'])){
                //     $qry->where('preferred_currency', $param['preferred_currency']);
                // }
            })
            ->when($param['operations_ids'], function ($qry) use($param) {
                if($param['operations_ids'] && is_array($param['operations_ids']))
                {
                    $qry->whereIn('id', $param['operations_ids']);
                } else if($param['operations_ids'] && !is_array($param['operations_ids']))
                {
                    $qry->where('id', $param['operations_ids']);
                }
            })
            ->when($pagination, function ($qry) use($pagination) {
                return $qry->paginate(config('constants.PER_PAGE'));
            }, function ($qry) {
                return $qry->get();
            });
    }

    public function getAllExplore($param, $pagination = true)
    {
        return Operation::has('seller')->OperationSelect()
            ->with([
            'references',
            'seller' => function ($qry) {
                $qry->select('id', 'slug', 'name','account_type', 'user_level', 'security_level', 'phone_number', 'city_id', 'profile_image', 'birth_date', 'registered_at', 'preferred_currency', 'address_verify','address_authorise_name');
                $qry->with('city:id,name');
            },
            'issuer' => function ($qry) {
                $qry->select('id','company_name', 'slug')->withAvg('ratings', 'rating_number')->withCount('ratings');
            },
            'issuer_bank' => function ($qry) {
                $qry->select('id','name', 'slug');
            },
            'documents' => fn($qry) => $qry->select('id', 'slug', 'operation_id', 'name', 'display_name', 'operation_id', 'path'),
            'supportingAttachments' => fn($qry) => $qry->select('id', 'slug', 'operation_id', 'name', 'display_name', 'operation_id', 'path'),
            'seller' => function ($qry) {
                $qry->withAvg('ratings', 'rating_number');
                $qry->withCount('ratings');
            },
            'offers' => fn($qry) => $qry->whereNotIn('offer_status', ['Approved', 'Completed'])->where('buyer_id', $this->user_login_id)->select('*'),
            ])
            /*   ->when(true, function($query) use ($param){
                $query->where(function($qry) use ($param){
                    $qry->whereDate('expired_at', '>',  date('Y-m-d'))->where('auto_expire', '1');
                });
            }) */
            /* ->when($param['search'] ?? false, function($query) use ($param){
                $query->where(function($qry) use ($param) {
                    $qry->whereHas('seller', function($qry) use ($param){
                        $qry->where('name',  'like', '%' . $param['search'] . '%');
                    })->orWhereHas('issuer', function($qry) use ($param){
                        $qry->where('name',  'like', '%' . $param['search'] . '%');
                    })->orWhereHas('issuer_bank', function($qry) use ($param){
                        $qry->where('name',  'like', '%' . $param['search'] . '%');
                    });
                });
            }) */

            /* code comment by sagar date 9/4/2023
            ->when($param['search_seller'] ?? false, function($query) use ($param){
                $query->where(function($qry) use ($param) {
                    $qry->whereHas('seller', function($qry) use ($param){
                        $qry->where('name',  'like', '%' . $param['search_seller'] . '%');
                    });
                });
            })
            ->when($param['search_payer'] ?? false, function($query) use ($param){
                $query->where(function($qry) use ($param) {
                    $qry->whereHas('issuer', function($qry) use ($param){
                        $qry->where('company_name',  'like', '%' . $param['search_payer'] . '%');
                    });
                });
            })
            ->when($param['search_bank'] ?? false, function($query) use ($param){
                $query->where(function($qry) use ($param) {
                    $qry->whereHas('issuer_bank', function($qry) use ($param){
                        $qry->where('name',  'like', '%' . $param['search_bank'] . '%');
                    });
                });
            })
            */
            ->when($param, function($query) use ($param){
                $query->where(function($qry) {
                    $qry->whereHas('offers', function($qry){
                        $qry->where('offer_status', 'Pending')->where('buyer_id', $this->user_login_id);
                    })->orWhereDoesntHave('offers', function($qry){
                        $qry->where('buyer_id', $this->user_login_id);
                    });
                });
            })
            ->when($param, function($query) use ($param){
                    if(isset($param['duration_date_range']) && !empty($param['duration_date_range'])) {
                    $response_date = app('common')->dateRangeExplode($param['duration_date_range'], '-');
                    $param_date['start_date'] = $response_date['start_date'] ?? date('Y-m-d');
                    $param_date['end_date'] = $response_date['end_date'] ?? null;
                    if($param_date['start_date'] && $param_date['end_date']) {
                        $query->where(function($qry) use ($param_date){
                            $qry->whereDate('issuance_date', '<=', $param_date['end_date'])->whereDate('expiration_date_document', '>=', $param_date['start_date']);
                        });
                    } else if(isset($param_date['start_date'])) {
                        $query->whereDate('expiration_date_document', '>=', $param_date['start_date'])->whereDate('issuance_date', '<=', $param_date['start_date']);
                    }
                }
            })
            ->whereDoesntHave('offers', function($qry){
                $qry->where('offer_status', 'Approved');
            })
            /* ->whereDoesntHave('offers', function($qry){
                $qry->where('buyer_id', Auth()->user()->id);
            }) */
            ->when($param, function($qry) use ($param){
              /*   if(!empty($param['operation_type'])){
                    $qry->whereIn('operation_type', $param['operation_type']);
                } */

                if(!empty($param['filer_type_doc'])){
                    $qry->where('operation_type', $param['filer_type_doc']);
                }
                if(!empty($param['preferred_currency'])){
                    $qry->whereIn('preferred_currency', $param['preferred_currency']);
                }
                if(!empty($param['responsibility'])){
                    $qry->whereIn('responsibility', $param['responsibility']);
                }
                if(!empty($param['preferred_payment_method'])){
                    $qry->whereIn('preferred_payment_method', $param['preferred_payment_method']);
                }
                if(!empty($param['mipo_verified'])){
                    $qry->where('mipo_verified', $param['mipo_verified']);
                }
                if(!empty($param['bcp'])){
                    $qry->where('bcp', '1');
                }
                if(!empty($param['inforconf'])){
                    $qry->where('inforconf', '1');
                }
                if(!empty($param['infocheck'])){
                    $qry->where('infocheck', '1');
                }
                if(!empty($param['criterium'])){
                    $qry->where('criterium', '1');
                }
                if(!empty($param['search_seller'])){
                    $qry->whereIn('seller_id', $param['search_seller']);
                }
                if(!empty($param['search_payer'])){
                    $qry->whereIn('issuer_id', $param['search_payer']);
                }
                if(!empty($param['search_bank'])){
                    $qry->whereIn('issuer_bank_id', $param['search_bank']);
                }

                if(!empty($param['responsibility']) && isset($param['responsibility'])){
                    $qry->whereIn('responsibility', $param['responsibility']);
                }
            })
            ->when(isset($param['offered']), function($qry) use ($param){
                if(isset($param['offered']) && $param['offered'] ==='1')
                {
                    $qry->whereHas('offers', function($qry){
                        $qry->where('buyer_id', $this->user_login_id);
                    });
                }
            })
            ->when(isset($param['favourites']), function($query) use ($param){
                if(isset($param['favourites']) && $param['favourites'] ==='1')
                {
                    $query->where(function($qry) use ($param){
                        $qry->whereHas('issuer.favorites', function($qry){
                            $qry->where('user_id', $this->user_login_id);
                        })->orWhereHas('seller.favorites', function($qry){
                            $qry->where('user_id', $this->user_login_id);
                        });
                    });
                }
            })
            ->when(isset($param['op_budget']), function($query) use ($param){
                $usd = config('constants.CURRENCY_TYPE')[0];
                if(isset($param['op_budget']) && $param['op_budget'] == $usd) {
                    if(isset($param['usd_min']) && isset($param['usd_max'])){
                        $query->whereBetween('amount', [$param['usd_min'], $param['usd_max']])
                        ->where('preferred_currency', $usd);
                    } else if(isset($param['usd_max'])) {
                        $query->where('amount', '<=', $param['usd_max'])->where('preferred_currency', $usd);
                    } else if(isset($param['usd_min'])) {
                        $query->where('amount', '>=', $param['usd_min'])->where('preferred_currency', $usd);
                    } else {
                        $query->where('preferred_currency', $usd);
                    }
                }
            })
            ->when(isset($param['op_budget']), function($query) use ($param){
                $gs = config('constants.CURRENCY_TYPE')[1];
                if(isset($param['op_budget']) && $param['op_budget'] == $gs) {
                    if(isset($param['gs_min']) && isset($param['gs_max'])){
                            $query->whereBetween('amount', [$param['gs_min'], $param['gs_max']])
                            ->where('preferred_currency', $gs);
                    } else if(isset($param['gs_max'])) {
                        $query->where('amount', '<=', $param['gs_max'])->where('preferred_currency', $gs);
                    } else if(isset($param['gs_min'])) {
                        $query->where('amount', '>=', $param['gs_min'])->where('preferred_currency', $gs);
                    } else {
                        $query->where('preferred_currency', $gs);
                    }
                }
            })
            ->when(isset($param['ratting']), function($query) use ($param){
                $query->where(function($qry) use ($param){
                $qry->select(DB::raw('FlOOR(AVG(rating_number)) as rate'))
                    ->from('ratings')
                    ->where('ratings.ratingable_type', get_class(User::first()))
                    ->whereColumn('operations.seller_id', 'ratings.ratingable_id');
                }, '>=', $param['ratting']);
            })
            ->when(isset($param['ratting_payer']), function($query) use ($param){
                $query->where(function($qry) use ($param){
                $qry->select(DB::raw('FlOOR(AVG(rating_number)) as rate'))
                    ->from('ratings')
                    ->where('ratings.ratingable_type', get_class(Issuer::first()))
                    ->whereColumn('operations.issuer_id', 'ratings.ratingable_id');
                }, '>=', $param['ratting_payer']);
            })
            ->when(isset($param['user_level']), function($query) use ($param) {
                if(!empty($param['user_level'])) {
                    $query->whereHas('seller', fn($qry) => $qry->whereIn('user_level', $param['user_level']));
                }
            })
            ->when($param['sort_column'], function($qry) use ($param){
                if($param['sort_column'] == 'amount'){
                    $qry->orderByRaw('CONVERT(amount, SIGNED) '. $param['sort_type']);
                } else if($param['sort_column'] == 'amount_asc' || $param['sort_column'] == 'amount_desc'){
                    $qry->orderByRaw('CONVERT(amount, SIGNED) '. $param['sort_type']);
                } else if($param['sort_column'] == 'DESC' || $param['sort_column'] == 'ASC'){
                    $qry->orderBy('id', $param['sort_column']);
                } else {
                    $qry->orderBy($param['sort_column'], $param['sort_type']);
                }
            })->when(Auth()->user(), function($qry){
                if($this->user_account_type == 'Enterprise') {
                    $qry->whereNotIn('seller_id', \DB::table('users')->where('enterprise_id', $this->user_login_id)->select('id')->pluck('id')->toArray());
                } else if($this->user_account_type == 'Individual') {
                    if($this->user_enterprise_id > 0) {
                        $qry->where('seller_id', '!=',$this->user_enterprise_id)
                        ->whereNotIn('seller_id', \DB::table('users')->where('enterprise_id',$this->user_enterprise_id)->select('id')->pluck('id')->toArray());
                    }
                }
            })->where('operations_status', 'Approved')->where('seller_id', '!=', $this->user_login_id)
            ->when($pagination, function ($qry) use($pagination) {
                    return $qry->paginate(config('constants.PER_PAGE'));
                }, function ($qry) {
                    return $qry->get();
            });
    }
}
?>
