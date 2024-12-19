<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Models\SupportingAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Offer;
use App\Models\UserLevel;
use App\Models\Operation;
use App\Models\DealsDocuments;

class DealsRepository extends Repository {

    public function getAll($param, $pagination = true)
    {
        return Offer::has('buyer')->has('operations')->with(['buyer'  => function($query) use ($param) {
                    $query->select('id', 'name')
                    ->with(['mi_coins_poinst' => function($qry) {
                        $qry->where('credit', 'Yes')->where('withdraw', 'No');
                    }]);
                },'operations' => function($query)  use ($param) {
                $query->select('seller_id', 'issuer_id', 'operation_number','operation_type', 'slug', 'amount', 'operations_status', 'preferred_currency', 'preferred_payment_method', 'issuance_date', 'expiration_date', 'extra_expiration_days');
                $query->with(['seller' => function($qry) use ($param) {
                    $qry->select('id', 'name')
                    ->with(['mi_coins_poinst' => function($qry) {
                        $qry->where('credit', 'Yes')->where('withdraw', 'No');
                    }]);
                },
                'issuer' => function($qry){
                    $qry->select('id', 'company_name','ruc_text_id', 'ruc_code_optional');
                }
            ]);
            }])
            ->when($param['search'] ?? false, function ($query) use ($param) {
                $query->where(function ($qry) use ($param) {
                    $qry->where('amount', 'like', '%' . $param['search'] . '%')
                        ->orWhere('offer_type', 'like', '%' . $param['search'] . '%')
                        ->orWhere('offer_status', 'like', '%' . $param['search'] . '%');
                })->orWhere(function ($qry) use ($param) {
                    $qry->whereHas('operations', function($qry) use($param) {
                        $qry->where(function ($qry) use ($param) {
                            $qry->where('amount', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_type', 'like', '%' . $param['search'] . '%');
                    });
                });
            });
        })
        ->when($param ?? false, function ($query) use ($param) {
            
            if(isset($param['buyer_id']) && !empty($param['buyer_id'])) {
                $query->where('buyer_id',  $param['buyer_id']);
            }

            if(isset($param['offer_status'])) {
                $query->where('offer_status',  $param['offer_status']);
            }

            if(isset($param['preferred_currency'])) {
                $query->whereHas('operations', function($qry) use($param) {
                    $qry->where('preferred_currency',  $param['preferred_currency']);
                });
            }
        })
        ->when($param, function ($query) use ($param) {
            if(isset($param['sort_column']) && isset($param['sort_type']) ){
                return $query->orderBy($param['sort_column'], $param['sort_type']);
            } else {
                return $query->orderBy('id', 'desc');
            }
        })->when(true, function ($query) use ($param) {
            if(isset($param['offer_status'])) {
                $query->where('offer_status',  $param['offer_status']);
            } else {
                $query->whereIn('offer_status', ['Approved', 'Completed']);
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

    public function getDetails($param)
    {
        return Offer::with(['buyer'  => function($query) use ($param) {
            $query->select('id', 'name');
            },
            'deals_contract',
            'deals_documents' => fn($qry) => $qry->select('id', 'offer_id', 'step_id', 'path', 'uploaded_by', 'uploaded_by_name', 'extension'),
            'deals_disputes' => function($qry){
                $qry->select('*')->with('disputed_user:id,name', 'resolved_user:id,name');
            },
            'deals_seog' => function($qry){
                $qry->select('id', 'offer_id', 'seog_name', 'path', 'extension', 'created_at')->orderBy('id', 'DESC');
            },
            'operations' => function($query)  use ($param) {
            $query->select('seller_id', 'operation_number','operation_type', 'slug', 'amount', 'operations_status', 'amount_requested', 'preferred_currency', 'preferred_payment_method', 'issuance_date', 'expiration_date', 'extra_expiration_days', 'issuer_id');
                $query->with(['seller' => function($qry) use ($param) {
                    $qry->select('id', 'name');
                },'issuer' => function($qry) use ($param) {
                    $qry->select('id', 'company_name');
                }
            ]);
            }])
            ->where('id', $param['offer_id'])
            ->first();
    }

    public function getDealsDocumentBYOfferId($param) 
    {
        return DealsDocuments::where('offer_id', $param['offer_id'])
            ->when($param, function ($query) use($param) {
                if(isset($param['user_type']) && !empty($param['user_type'])) {
                    $query->where('uploaded_by_name', $param['user_type']);
                }
                if(isset($param['upload_type']) && !empty($param['upload_type'])) {
                    $query->where('upload_type', $param['upload_type']);
                }
            })
            ->orderBy('id', 'desc')
            ->get();
    }
}
?>
