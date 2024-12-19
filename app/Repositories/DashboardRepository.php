<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Models\SupportingAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OperationsLogs;
use App\Models\User;
use App\Models\Rating;
use App\Models\Offer;
use App\Models\Operation;
use App\Models\MiCoinsPoint;
use Carbon\Carbon;

class DashboardRepository extends Repository {

    public function getPichartData($param = [])
    {
        $pichart_qry = Operation::select('operation_type','id', 'amount', 'preferred_currency',
          /*   DB::raw('count(operation_type) as operation_type_total'),
            DB::raw('count(id) as id_total'),
            DB::raw('count(preferred_currency) as preferred_currency_total')  */
            )->with('offers', function($qry) {
                $qry->whereIn('offer_status', ['Approved', 'Completed']);
            })
            ->whereHas('offers', function($query) use ($param) {
                
                if(isset($param['preferred_dashboard']) && $param['preferred_dashboard'] == 'Investor') {
                    $query->where('buyer_id', '=', $this->user_login_id)->whereIn('offer_status', ['Approved', 'Completed']);
                }

                if(isset($param['preferred_dashboard']) && $param['preferred_dashboard'] == 'Borrower') {
                    $query->where('buyer_id', '!=',  $this->user_login_id)->whereIn('offer_status', ['Approved', 'Completed']);
                    $query->whereHas('operations' , function($qry){
                        $qry->where('seller_id', $this->user_login_id);
                    });
                }

                $query->when(true, function($query) use ($param) {
                    if(isset($param['start_date']) && isset($param['end_date'])) {
                        $query->where(function($qry) use ($param) {
                            $qry->whereDate('created_at', '>=', $param['start_date'])->whereDate('created_at', '<=', $param['end_date']);
                        });
                    } else if(isset($param['start_date'])) {
                        $query->whereDate('created_at', '>=', $param['end_date']);
                    }
                });
            })
            ->when($param, function($query) use ($param){
                if(isset($param['currency_type']) && !empty($param['currency_type'])) {
                    $query->where('preferred_currency', $param['currency_type']);
                }
                if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                    $query->where('issuer_id', $param['issuer_id']);
                }
            })
            // ->groupBy('operation_type')->groupBy('id')->groupBy('preferred_currency')
            ->get();
            if(isset($param['page_name']) && $param['page_name'] == 'dashboard') {
                if(isset($param['currency_type']) && $param['currency_type'] == $this->dollar_name) {
                    return array('data' => [
                        $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_name)->count(),
                        $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_name)->count(),
                        $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_name)->count(),
                        $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_name)->count(),
                        ],
                        'labels' => [
                        __('Invoice') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                        __('Cheque') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                        __('Contract') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                        __('Other') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                        ],
                    );
                } else {
                    return array('data' => [
                        $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->count(),
                        $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->count(),
                        $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->count(),
                        $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->count(),
                        ],
                        'labels' => [
                        __('Invoice') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->pluck('amount')->sum().'k',
                        __('Cheque').$this->pyg_sign. $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->pluck('amount')->sum().'k',
                        __('Contract') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->pluck('amount')->sum().'k',
                        __('Other') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->pluck('amount')->sum().'k',
                        ],
                    );
                }
            } else if(isset($param['page_name']) && $param['page_name'] == 'company_profile') {
                return array('data' => [
                    $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_name)->count(),
                    $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->count(),
                    $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_name)->count(),
                    $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->count(),
                    $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_name)->count(),
                    $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->count(),
                    $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_name)->count(),
                    $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->count(),
                    ],
                    'labels' => [
                    __('Invoice') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    __('Invoice') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    __('Cheque') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    __('Cheque') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    __('Contract') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    __('Contract') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    __('Other') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    __('Other') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                    ],
                );
            }
    }

    public function operationStatus($param = [])
    {
        return Operation::select('operations_status', DB::raw('count(operations_status) as total_operations_status'))
                ->when($param, function($query) use ($param){
                    if(isset($param['user_id']) && !empty($param['user_id'])) {
                        $query->where('seller_id', $param['user_id']);
                    }
                    if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                        $query->where('issuer_id', $param['issuer_id']);
                    }
                })->groupBy('operations_status')->get();
    }

    public function offerStatus($param = [])
    {
        return Offer::select('offer_status', DB::raw('count(offer_status) as total_offer_status'))
                ->whereHas('operations' , function($query) use($param) {
                    if(isset($param['user_id']) && !empty($param['user_id'])) {
                        $query->where('seller_id', $param['user_id']);
                    }
                    if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                        $query->where('issuer_id', $param['issuer_id']);
                    }
                })->groupBy('offer_status')->get();
    }

    public function dealDisputesStatus($param = [])
    {
        return Offer::select('id', 'slug', 'offer_status',)
                ->with('deals_disputes')
                ->whereHas('operations' , function($query) use($param) {
                    if(isset($param['user_id']) && !empty($param['user_id'])) {
                        $query->where('seller_id', $param['user_id']);
                    }
                    if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                        $query->where('issuer_id', $param['issuer_id']);
                    }
                })->first();
    }

    public function averageRetention($param = [])
    {
        return  Offer::select('id', 'retention')
                ->whereHas('operations', function($query) use ($param) {
                    if(isset($param['user_id']) && !empty($param['user_id'])) {
                        $query->where('seller_id', $param['user_id']);
                    }
                    if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                        $query->where('issuer_id', $param['issuer_id']);
                    }
                })->where('offer_status', 'Approved')->get()->pluck('retention')->avg();
    }

    public function averageAmountRetention($param = [])
    {
        return  Offer::select('id', 'retention')
                ->whereHas('operations', function($query) use ($param) {
                    if(isset($param['user_id']) && !empty($param['user_id'])) {
                        $query->where('seller_id', $param['user_id']);
                    }
                    if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                        $query->where('issuer_id', $param['issuer_id']);
                    }
                })->where('offer_status', 'Approved')->get()->pluck('retention')->sum();
    }

    public function dealsBuyer($param = [])
    {
        return Offer::select('offer_status', 'is_mipo_plus')
            ->where('buyer_id', $this->user_login_id)
            ->get();
    }

    public function dealsSeller($param = [])
    {
        return Offer::select('offer_status', 'is_mipo_plus')
            ->where('buyer_id', '!=', $this->user_login_id)
            ->whereHas('operations', function($qry) use ($param) {
                $qry->where('seller_id', $this->user_login_id);
            })->get();
    }

    public function getBarchartData($param = [])
    {
        return Offer::select(
            'id',
            'amount',
            DB::raw("(DATE_FORMAT(updated_at, '%m-%Y')) as month_year"),
            DB::raw("(DATE_FORMAT(updated_at, '%M')) as month_name"),
            DB::raw("sum(amount) as total_amount")
            )
            /*  ->when($param, function($query) use ($param) {
                if($param['start_date'] && $param['start_date']) {
                    $query->where(function($qry) use ($param){
                        $qry->whereDate('updated_at', '>=', $param['start_date'])->whereDate('updated_at', '<=', $param['end_date']);
                    });
                }
            }) */
            ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%m-%Y')"), 'id', 'amount','month_name')
            ->orderBy('updated_at')
            ->get();

    }

    public function getDashboarDeals($param = [])
    {
        return Offer::with([
                'buyer' => function ($qry) {
                    $qry->select('id', 'name','account_type', 'user_level', 'security_level', 'phone_number', 'city_id', 'profile_image', 'birth_date', 'registered_at', 'preferred_currency')
                        ->with('city:id,name');
                },
                'operations' => function ($qry) {
                    $qry->OperationSelect();
                    $qry->with('issuer:id,company_name');
                    $qry->with('seller:id,name');
                },
            ])->withCount('deals_documents')
            ->when(true, function($query) use ($param) {
                if($param['preferred_dashboard'] == 'Investor') {
                    $query->where('buyer_id',  $this->user_login_id);
                } else if($param['preferred_dashboard'] == 'Borrower') {
                    $query->where('buyer_id', '!=',  $this->user_login_id);
                    $query->whereHas('operations', function($qry){
                        $qry->where('seller_id', $this->user_login_id);
                    });
                }
            })
            ->when(true, function($query) use ($param) {
                if(isset($param['start_date']) && isset($param['end_date'])) {
                    $query->where(function($qry) use ($param) {
                        $qry->whereDate('created_at', '>=', $param['start_date'])->whereDate('created_at', '<=', $param['end_date']);
                    });
                } else if(isset($param['start_date'])) {
                    $query->whereDate('created_at', '>=', $param['end_date']);
                }
            })
            ->when(isset($param) , function($query) use ($param) {
                    $query->whereHas('operations', function($qry) use ($param) {
                        if(isset($param['currency_type']) && !empty($param['currency_type']))  {
                            $qry->where('preferred_currency', $param['currency_type']);
                        }
                        if(isset($param['operation_type']) && !empty($param['operation_type']))  {
                            if(is_array($param['operation_type'])) {
                                $qry->whereIn('operation_type', $param['operation_type']);
                            } else {
                                $qry->where('operation_type', $param['operation_type']);
                            }
                        }
                    });
            })->when(isset($param) , function($query) use ($param) {
                if(isset($param['min']) && isset($param['max']))  {
                    $query->whereBetween('amount', [$param['min'], $param['max']]);
                } else if(isset($param['min']))  {
                    $query->where('amount', '>=', $param['min']);
                } else if(isset($param['max']))  {
                    $query->where('amount', '<=', $param['max']);
                }
            })
            ->when(isset($param) , function($query) use ($param) {
                if(isset($param['sort_type_deals']) && !empty($param['sort_type_deals']))  {
                    $query->orderBy('id', $param['sort_type_deals']);
                } else {
                    $query->orderBy('id', 'DESC');
                }
            })->when(true, function($query) use ($param) {
                if(isset($param['offer_status_all']) && $param['offer_status_all'] == true) {
                    $query->whereIn('offer_status', ['Approved', 'Completed', 'Pending', 'Counter','Rejected']);
                } else {
                    $query->whereIn('offer_status', ['Approved', 'Completed', 'Pending']);
                }
            })
            ->get();
    }

    public function getDashboarDealsLastMonth($param = [])
    {
        return Offer::with([
                'buyer' => function ($qry) {
                    $qry->select('id', 'name','account_type', 'user_level', 'security_level', 'phone_number', 'city_id', 'profile_image', 'birth_date', 'registered_at', 'preferred_currency')
                        ->with('city:id,name');
                },
                'operations' => function ($qry) {
                    $qry->OperationSelect();
                    $qry->with('issuer:id,company_name');
                    $qry->with('seller:id,name');
                },
            ])->withCount('deals_documents')
            ->when($param, function($query) use ($param) {
                if($param['preferred_dashboard'] == 'Investor') {
                    // $query->has('buyer');
                    $query->where('buyer_id', '=', $this->user_login_id);
                }
                if($param['preferred_dashboard'] == 'Borrower') {
                    $query->where('buyer_id', '!=',  $this->user_login_id);
                    $query->whereHas('operations' , function($qry){
                        $qry->where('seller_id', $this->user_login_id);
                    });
                }
            })->when(true, function($query) use ($param) {
                    if(isset($param['last_month_start']) && isset($param['last_month_end'])) {
                        $query->where(function($qry) use ($param) {
                            $qry->whereDate('created_at', '>=', $param['last_month_start'])->whereDate('created_at', '<=', $param['last_month_end']);
                        });
                    } else if(isset($param['last_month_start'])) {
                        $query->whereDate('created_at', '>=', $param['last_month_start']);
                    }
            })
            ->when(isset($param) , function($query) use ($param) {
                    $query->whereHas('operations', function($qry) use ($param) {
                        if(isset($param['currency_type']) && !empty($param['currency_type']))  {
                            $qry->where('preferred_currency', $param['currency_type']);
                        }
                        if(isset($param['operation_type']) && !empty($param['operation_type']))  {
                            if(is_array($param['operation_type'])) {
                                $qry->whereIn('operation_type', $param['operation_type']);
                            } else {
                                $qry->where('operation_type', $param['operation_type']);
                            }
                        }
                    });
            })->when(isset($param) , function($query) use ($param) {
                if(isset($param['min']) && isset($param['max']))  {
                    $query->whereBetween('amount', [$param['min'], $param['max']]);
                } else if(isset($param['min']))  {
                    $query->where('amount', '>=', $param['min']);
                } else if(isset($param['max']))  {
                    $query->where('amount', '<=', $param['max']);
                }
            })
            ->when(isset($param) , function($query) use ($param) {
                if(isset($param['sort_type_deals']) && !empty($param['sort_type_deals']))  {
                    $query->orderBy('id', $param['sort_type_deals']);
                } else {
                    $query->orderBy('id', 'DESC');
                }
            })->whereIn('offer_status', ['Approved', 'Completed'])
            ->get();
    }

    public function getDashboarEnterprise($param = [])
    {
        $individual_user_ids = User::select('id', 'name')->where('enterprise_id', $this->user_login_id)->pluck('id')->toArray();
        $param['individual_user_ids'] = $individual_user_ids;

        return User::select('id', 'enterprise_id', 'name','account_type', 'user_level', 'security_level', 'phone_number', 'city_id', 'profile_image', 'birth_date', 'registered_at', 'preferred_currency')
            ->with([
                'city:id,name',
                'operations' => fn($query) => $query->OperationSelect(),
                'operations.offers' => fn($query) => $query->select('*'),
            ])
            // ->whereHas('operations' , function($query) use ($param){
            //     $query->where('preferred_currency', $param['currency_type'])->whereIn('seller_id', $param['individual_user_ids']);
            // })
            // ->when($param, function($query) use ($param) {
            //     $query->whereHas('operations.offers' , function($qry) use ($param){
            //         if($param['preferred_dashboard'] == 'Investor') {
            //             $qry->whereIn('offer_status', ['Approved', 'Completed'])->has('buyer');
            //         }
            //         if($param['preferred_dashboard'] == 'Borrower') { 
            //             $qry->whereIn('offer_status', ['Approved', 'Completed'])->whereNotIn('buyer_id', $param['individual_user_ids']);
            //         }
            //     });
            // })
            ->whereIn('id', $param['individual_user_ids'])->get();
    }

    public function getDashboarLineChart($param = [])
    {
        return Offer::with([
                'operations' => function ($qry) {
                    $qry->select('operation_number', 'operation_type', 'preferred_currency', 'amount');
                },
            ])
            ->when($param, function($query) use ($param) {
                if($param['preferred_dashboard'] == 'Investor') {
                    // $query->has('buyer');
                    $query->where('buyer_id', '=', $this->user_login_id);
                }
                if($param['preferred_dashboard'] == 'Borrower') {
                    $query->where('buyer_id', '!=',  $this->user_login_id);
                    $query->whereHas('operations' , function($qry){
                        $qry->where('seller_id', $this->user_login_id);
                    });
                }
            })->when(true, function($query) use ($param) {
                if(isset($param['start_date']) && isset($param['end_date'])) {
                    $query->where(function($qry) use ($param) {
                        $qry->whereDate('created_at', '>=', $param['start_date'])->whereDate('created_at', '<=', $param['end_date']);
                    });
                } else if(isset($param['start_date'])) {
                    $query->whereDate('created_at', '>=', $param['end_date']);
                }
            })
            ->when(isset($param) , function($query) use ($param) {
                $query->whereHas('operations', function($qry) use ($param) {
                    if(isset($param['currency_type']) && !empty($param['currency_type']) && $param['currency_type'] != 'both')  {
                        $qry->where('preferred_currency', $param['currency_type']);
                    }
                    if(isset($param['operation_type']) && !empty($param['operation_type']))  {
                        if(is_array($param['operation_type'])) {
                            $qry->whereIn('operation_type', $param['operation_type']);
                        } else {
                            $qry->where('operation_type', $param['operation_type']);
                        }
                    }
                });
            })->when(isset($param) , function($query) use ($param) {
                if(isset($param['min']) && isset($param['max']))  {
                    $query->whereBetween('amount', [$param['min'], $param['max']]);
                } else if(isset($param['min']))  {
                    $query->where('amount', '>=', $param['min']);
                } else if(isset($param['max']))  {
                    $query->where('amount', '<=', $param['max']);
                }
            })
            ->when(isset($param) , function($query) use ($param) {
                if(isset($param['sort_type_deals']) && !empty($param['sort_type_deals']))  {
                    $query->orderBy('id', $param['sort_type_deals']);
                } else {
                    $query->orderBy('id', 'DESC');
                }
            })->whereIn('offer_status', ['Approved', 'Completed'])
            ->get();
    }

    public function getDashboarBestSeller($param = [])
    {
        $individual_user_ids = User::select('id', 'name')->where('enterprise_id', $this->user_login_id)->pluck('id')->toArray();
        $param['individual_user_ids'] = $individual_user_ids;

        return Offer::join('offer_operations', 'offers.id', 'offer_operations.offer_id')
                ->join('operations', 'offer_operations.operation_id', 'operations.id')
                ->join('users', 'operations.seller_id', 'users.id')
                ->where('operations.preferred_currency', $param['currency_type'])
                ->whereIn('users.id', $param['individual_user_ids'])
                ->when(true, function($query) use ($param) {
                    if($param['preferred_dashboard'] == 'Investor') {
                        $query->whereIn('offers.offer_status', ['Approved', 'Completed'])->has('buyer');
                    }
                    if($param['preferred_dashboard'] == 'Borrower') { 
                        $query->whereIn('offers.offer_status', ['Approved', 'Completed'])->whereNotIn('offers.buyer_id', $param['individual_user_ids']);
                    }
                })
                ->select(DB::raw('SUM(operations.amount) AS operation_amount'), DB::raw('MAX(operations.preferred_currency) AS preferred_currency'), DB::raw('max(users.name) AS best_seller'),  DB::raw('SUM(offers.amount) AS offers_amount'))
                // ->groupBy('operations.seller_id')
                ->first();
    }

    public function averageOperationValue($param = [])
    {
        return Operation::select('amount', 'preferred_currency')->where('operations_status', 'Approved')
            ->when($param, function($query) use ($param){
                if(isset($param['user_id']) && !empty($param['user_id'])) {
                    $query->where('seller_id', $param['user_id']);
                }
                if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                    $query->where('issuer_id', $param['issuer_id']);
                }
            })->get();
    }

    public function averageRatingDays($param = [])
    {
        return Rating::select('feedback_title')->where('user_id', $param['user_id'])->where('ratingable_type', 'App\Models\User')
            ->when(true, function($query) use ($param) {
                if(isset($param['start_date']) && isset($param['end_date'])) {
                    $query->where(function($qry) use ($param) {
                        $qry->whereDate('created_at', '>=', $param['start_date'])->whereDate('created_at', '<=', $param['end_date']);
                    });
                } else if(isset($param['start_date'])) {
                    $query->whereDate('created_at', '>=', $param['end_date']);
                }
            })
            ->avg('feedback_title');
    }

    public function getDueDashboarDeals($param = [])
    {
        return Offer::with([
            'operations' => function ($qry) {
                $qry->OperationSelect();
            },
        ])
        ->when($param, function($query) use ($param) {
            if($param['preferred_dashboard'] == 'Investor') {
                $query->where('buyer_id',  $this->user_login_id);
            }
            if($param['preferred_dashboard'] == 'Borrower') {
                $query->where('buyer_id', '!=',  $this->user_login_id);
                $query->whereHas('operations', function($qry){
                    $qry->where('seller_id', $this->user_login_id);
                });
            }
        })
        ->when(true, function($query) use ($param) {
            if(isset($param['start_date']) && isset($param['end_date'])) {
                $query->where(function($qry) use ($param) {
                    $qry->whereDate('created_at', '<=', $param['end_date'])->whereDate('created_at', '>=', $param['start_date']);
                });
            } else if(isset($param['start_date'])) {
                $query->whereDate('created_at', '<=', $param['end_date']);
            }
        })
        /*->when(true, function($query) use ($param) {
            if(isset($param['due_seven_days'])) {
                $query->whereDate('expires_at', '>', $param['due_seven_days']);
            } else if(isset($param['due_fifteen_days'])) {
                $query->whereDate('expires_at', '>', $param['due_fifteen_days']);
            } else if(isset($param['due_thirty_days'])) {
                $query->whereDate('expires_at', '>', $param['due_thirty_days']);
            } else if(isset($param['exp_thirty_days'])) {
                $query->whereDate('expires_at', '<', $param['exp_thirty_days']);
            }
        }) */
        ->when(isset($param) , function($query) use ($param) {
            $query->whereHas('operations', function($qry) use ($param) {
                if(isset($param['currency_type']) && !empty($param['currency_type']))  {
                    $qry->where('preferred_currency', $param['currency_type']);
                }
            });
        })
        ->where('offer_status', 'Approved')
        ->where('is_cashed_buyer', 'No')
        // ->whereIn('offer_status', ['Approved', 'Completed'])
        ->get();
    }

    public function getDashboardMiCoinsPoint($param = [])
    {
        return MiCoinsPoint::select('id', 'user_id', 'points', 'credit', 'withdraw')->where('user_id', $param['user_id'])->get();
    }

    public function averageIssuerRatingDays($param = [])
    {
        return Rating::select('issuers_title')->where('ratingable_id', $param['issuer_id'])->where('ratingable_type', 'App\Models\Issuer')
            ->when(true, function($query) use ($param) {
                if(isset($param['start_date']) && isset($param['end_date'])) {
                    $query->where(function($qry) use ($param) {
                        $qry->whereDate('created_at', '>=', $param['start_date'])->whereDate('created_at', '<=', $param['end_date']);
                    });
                } else if(isset($param['start_date'])) {
                    $query->whereDate('created_at', '>=', $param['end_date']);
                }
            })
            ->avg('issuers_title');
    }
    
    public function averageDiscount($param = [])
    {
        $result =  Offer::select('id', 'amount')
        ->withSum('operations', 'amount')
        ->whereHas('operations', function($query) use ($param) {
            if(isset($param['user_id']) && !empty($param['user_id'])) {
                $query->where('seller_id', $param['user_id']);
            }
            if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                $query->where('issuer_id', $param['issuer_id']);
            }
        })->where('offer_status', 'Approved')->get();
        
        $offerAmount = $result->sum('amount');
        $operationAmount = $result->sum('operations_sum_amount');
        $averageDiscount = 0;
        if($offerAmount > 0 &&  $operationAmount > 0) {
            $diff = ($operationAmount - $offerAmount);
            $averageDiscount = (($diff / $result->count()));
        }
        return round($averageDiscount, 2);
    }
    
    public function getUserProfilePichartData($param = [])
    {
        $pichart_qry =  Operation::select('operation_type', 'id', 'preferred_currency', 'amount')
            ->when($param, function($qry) use ($param) {
                    if(isset($param['user_id']) && $param['user_id'] > 0) {
                        $qry->where('seller_id', $param['user_id']);
                    }
                    if(isset($param['issuer_id']) && !empty($param['issuer_id'])) {
                        $qry->where('issuer_id', $param['issuer_id']);
                    }
            })->doesntHave('offers')
            ->where('operations_status', 'Approved')
            ->get();
    
            return array('data' => [
                $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_name)->count(),
                $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->count(),
                $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_name)->count(),
                $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->count(),
                $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_name)->count(),
                $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->count(),
                $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_name)->count(),
                $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->count(),
                ],
                'labels' => [
                __('Invoice').' '.$this->dollar_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                __('Invoice').' ' .$this->pyg_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->sum('amount').'k',
                __('Check').' '. $this->dollar_sign. $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                __('Check').' '. $this->pyg_sign.  $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->pluck('amount')->sum().'k',
                __('Contract').' ' .$this->dollar_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                __('Contract').' ' .$this->pyg_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->pluck('amount')->sum().'k',
                __('Other').' ' .$this->dollar_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_name)->pluck('amount')->sum().'k',
                __('Other').' ' .$this->pyg_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->pluck('amount')->sum().'k',
                ],
            );
    }

}
?>
