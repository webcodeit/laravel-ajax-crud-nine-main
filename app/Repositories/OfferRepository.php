<?php 

namespace App\Repositories;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Offer;
use App\Models\Operation;
use App\Models\OfferOperation;
use App\Models\CounterOffer;

class OfferRepository extends Repository {

    public function getAll($param, $pagination = true)
    {
        return Offer::has('buyer')->has('operations')->with('buyer', 'operations', 'operations.seller:id,name')
            ->when(isset($param['search']), function ($querys) use ($param) {
                $querys->where(function ($query) use ($param) {
                    $query->where(function ($qry) use ($param) {
                        $qry->where('amount', 'like', '%' . $param['search'] . '%')
                            ->orWhere('net_profit', 'like', '%' . $param['search'] . '%')
                            ->orWhere('preferred_payment_method', 'like', '%' . $param['search'] . '%')
                            ->orWhere('offer_status', 'like', '%' . $param['search'] . '%')
                            ->orWhere('offer_type', 'like', '%' . $param['search'] . '%');
                    })->orWhere(function ($query) use ($param) {
                        $query->whereHas('buyer', function ($qry) use($param) {
                            $qry->where('name', 'like', '%' . $param['search'] . '%')
                            ->orWhere('first_name', 'like', '%' . $param['search'] . '%')
                            ->orWhere('last_name', 'like', '%' . $param['search'] . '%');
                        })->orWhereHas('operations', function ($qry) use($param) {
                            $qry->where('amount', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('preferred_payment_method', 'like', '%' . $param['search'] . '%')
                            ->orWhere('check_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('issuer_company_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_type', 'like', '%' . $param['search'] . '%');
                        });
                    });
                });
            })->when(true, function ($query) use ($param) {
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
                    return $query->orderBy('id', $param['sort_type']);
                } else {
                    return $query->orderBy('id', 'desc');
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

    public function OfferBySellerName($param)
    {
        return Operation::select('id','seller_id')
            ->with([
                'offers:id,buyer_id',
                'seller' => function($qry) {
                    $qry->select('id', 'name', 'first_name', 'last_name');
                }
            ])
            ->whereHas('offers', function($qry) use ($param) {
                $qry->where('buyer_id', $param['login_user_id'])->where('offer_status', '=', 'Approved');
            })->when(isset($param['search']), function ($qry) use ($param) {
                if (isset($param['search']) && !empty($param['search'])) {
                    $qry->whereHas('seller', function($qry) use ($param) {
                        $search = $param['search'];
                        $qry->where(function($qry) use ($search){
                            return $qry->where('name', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                        });
                    });
                }
            })->get();
    }

    public function OfferByIssuersName($param)
    {
        return Operation::select('id','seller_id','issuer_id')
            ->with([
                'issuer:id,company_name',
            ])
            ->whereHas('offers', function($qry) use ($param) {
                $qry->where('buyer_id', $param['login_user_id'])->where('offer_status', '=', 'Approved');
            })->when(isset($param['search']), function ($qry) use ($param) {
                if (isset($param['search']) && !empty($param['search'])) {
                    $qry->whereHas('issuer', function($qry) use ($param) {
                        $search = $param['search'];
                        return $qry->where('company_name', 'like', '%' . $search . '%');
                    });
                }
            })->get();
    }

    public function offerByOfferOperation($param)
    {
        return OfferOperation::where('offer_id', $param['offer_id'])->get();
    }

    public function offerDetailsById($param)
    {
        return Offer::where('id', $param['offer_id'])
        ->with(['buyer' => function($query) {
            $query->select('id', 'name');
        }])->first();
    }

    public function offeredOperationsWeb($param, $pagination = true)
    {
        return Offer::with([
            'counter_offers',
            'buyer'=> function($query) {
                $query->select('id', 'name');
            },
            'operations' => function($query) {
                $query->OperationSelect()
                ->with([
                    'seller' => fn($qry) => $qry->select('id', 'city_id', 'name', 'profile_image','address_verify', 'address_authorise_name', 'security_level'),
                    'issuer' => fn($qry) => $qry->select('id', 'company_name'),
                    'documents' => fn($qry) => $qry->select('id', 'operation_id', 'name', 'display_name', 'path'),
                    'supportingAttachments' => fn($qry) => $qry->select('id', 'operation_id', 'name', 'display_name', 'path'),
                    'seller' => fn($qry) => $qry->withAvg('ratings', 'rating_number')->withCount('ratings'),
                    'seller.city' => fn($qry) => $qry->select('id', 'name'),
                    'offers' => function($qry) {
                        $qry->select('*')
                        ->whereIn('offer_status', ['Pending', 'Counter'])->orderBy('amount' , 'DESC')->first();
                    },
                ]);
            }
        ])
        ->whereHas('operations', function ($qry) {
            $qry->where('is_offered', 0);
        })
        ->where('buyer_id', $this->user_login_id)
        // ->where('is_buyer_deals_contract', 'No')
        ->whereNotIn('offer_status', ['Approved', 'Completed'])
        ->when($param['sort_column'], function ($qry) use ($param) {
            if ($param['sort_column'] == 'amount') {
                $qry->orderByRaw('CONVERT(amount, SIGNED) ' . $param['sort_type']);
            } else {
                $qry->orderBy($param['sort_column'], $param['sort_type']);
            }
        })->when($pagination, function ($query) use($param) {
            if(isset($param['per_page']) && $param['per_page']!=''){
                return $query->paginate($param['per_page']);
            }
        }, function ($query) {
            return $query->get();
        });
    }

    public function offerContractDetailsById($param){
        return  Offer::with([
            'buyer'=> function($query) {
                $query->select('id', 'name', 'address')->with('bank_details');
            },
            'deals_contract',
            'operations' => function($query) {
                $query->OperationSelect()
                ->with([
                    'seller' => fn($qry) => $qry->select('id', 'name', 'profile_image','address_verify', 'address_authorise_name', 'security_level', 'address')->with('bank_details'),
                    'issuer' => fn($qry) => $qry->select('id', 'company_name'),
                    // 'documents' => fn($qry) => $qry->select('id', 'operation_id', 'name', 'display_name', 'path'),
                    // 'supportingAttachments' => fn($qry) => $qry->select('id', 'operation_id', 'name', 'display_name', 'path'),
                ]);
            }
        ])->where('id',  $param['offer_id'])->first();
    }
}
?>
