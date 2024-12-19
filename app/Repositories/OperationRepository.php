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
use App\Models\UserLevel;
use App\Models\Issuer;
use App\Models\Operation;
use App\Models\Document;
use App\Models\OperationProgressStatusFile;
use App\Models\OperationsAdminStaffFile;
use Carbon\Carbon;

class OperationRepository extends Repository {

    public function getAll($param, $pagination = true)
    {
        return Operation::has('seller')->OperationSelect()
            ->with([
                'seller' => function($qry){
                    $qry->select('id', 'name', 'ruc_tax_id');
                },
                'issuer' => function($qry){
                    $qry->select('id', 'company_name','ruc_text_id', 'ruc_code_optional');
                },
                'offers'
            ])->withCount(['offers' => function($qry){
                $qry->where('is_offered', '1');
            }])
            ->when(isset($param['search']), function ($querys) use ($param) {
                $querys->where(function ($query) use ($param) {
                    $query->where(function ($qry) use ($param) {
                        $qry->where('amount', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('preferred_currency', 'like', '%' . $param['search'] . '%')
                            ->orWhere('mipo_verified', 'like', '%' . $param['search'] . '%')
                            ->orWhere('preferred_payment_method', 'like', '%' . $param['search'] . '%')
                            ->orWhere('check_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('issuer_company_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_type', 'like', '%' . $param['search'] . '%');
                    })->orWhere(function ($query) use ($param) {
                        $query->whereHas('seller', function ($qry) use($param) {
                            $qry->where('name', 'like', '%' . $param['search'] . '%')
                            ->orWhere('first_name', 'like', '%' . $param['search'] . '%')
                            ->orWhere('last_name', 'like', '%' . $param['search'] . '%');
                        })->orWhereHas('issuer', function ($qry) use($param) {
                            $qry->where('company_name', 'like', '%' . $param['search'] . '%')->orWhere('ruc_text_id', 'like', '%' . $param['search'] . '%');;
                        })->orWhereHas('issuer_bank', function ($qry) use($param) {
                            $qry->where('name', 'like', '%' . $param['search'] . '%');
                        });
                    });
                });
            })
            ->when($param, function ($query) use ($param) {
                if(isset($param['operation_type']) && !empty($param['operation_type']) ){
                    $query->where('operation_type', $param['operation_type']);
                }
                if(isset($param['operations_status']) && !empty($param['operations_status']) ){
                    $query->where('operations_status', $param['operations_status']);
                }
                if(isset($param['action']) && $param['action'] == 'export' ) {
                    if(isset($param['operation_ids']) && !empty($param['operation_ids'])) {
                        $query->whereIn('id', explode(',', $param['operation_ids']));
                    }
                }
            })
            ->when($param, function ($query) use ($param) {
                if(isset($param['sort_column']) && isset($param['sort_type']) ){
                    return $query->orderBy($param['sort_column'], $param['sort_type']);
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

    public function getAllOperationWeb($param, $pagination = true)
    {
        return Operation::select('operations.*')
            ->with([
            'documents' => function($query) {
                $query->select('id', 'slug', 'operation_id', 'name', 'display_name', 'path', 'extension');
            }, 
            'supportingAttachments' => function($query) {
                $query->select('id', 'slug', 'operation_id', 'name', 'display_name', 'path', 'extension');
            },
            'issuer' => function($query) {
                $query->select('id', 'slug',  'company_name');
            },
            'issuer_bank' => function($query) {
                $query->select('id', 'slug',  'name');
            },
            'seller' => function($query) {
                $query->select('id', 'slug',  'name')->withAvg('ratings', 'rating_number')->withCount('ratings');
            },
            ])->withCount(['offers' => function($qry){
                $qry->where('is_offered', '1');
            }])
            ->where('seller_id', $this->user_login_id)
            ->when($param, function ($qry) use ($param) {
                if (!empty($param['operation_type'])) {
                    $qry->whereIn('operation_type', $param['operation_type']);
                }
                if (!empty($param['preferred_currency'])) {
                    $qry->whereIn('preferred_currency', $param['preferred_currency']);
                }
                if (!empty($param['responsibility'])) {
                    $qry->whereIn('responsibility', $param['responsibility']);
                }
                if (!empty($param['preferred_payment_method'])) {
                    $qry->whereIn('preferred_payment_method', $param['preferred_payment_method']);
                }
                if (!empty($param['operation_status'])) {
                    if($param['operation_status'] == 'Unsold') {
                        $qry->where('operations_status', '!=', 'Approved')->whereDate('expiration_date', '<', date('Y-m-d'));
                    } else {
                        $qry->where('operations_status', $param['operation_status']);
                    }
                }
                if (isset($param['mipo_verified']) && $param['mipo_verified'] == 'Yes') {
                    $qry->where('mipo_verified', 'Yes');
                }
                if (!empty($param['add_tags'])) {
                    $qry->when($param, function ($qry) use ($param) {
                        $qry->whereHas('tags', function ($q) use ($param) {
                            $q->whereIn('id', $param['add_tags']);
                        });
                    });
                }
            })
            ->when($param, function($query) use ($param){
                if(isset($param['duration_date_range']) && !empty($param['duration_date_range'])) {
                    $response_date = app('common')->dateRangeExplode($param['duration_date_range'], '-');
                    $param_date['start_date'] = $response_date['start_date'] ?? date('Y-m-d');
                    $param_date['end_date'] = $response_date['end_date'] ?? null;
                    if($param_date['start_date'] && $param_date['end_date']) {
                        $query->where(function($qry) use ($param_date){
                            $qry->where(function($qry) use ($param_date){
                                $qry->whereDate('issuance_date', '<=', $param_date['end_date'])->whereDate('expiration_date_document', '>=', $param_date['start_date']);
                            })->orWhere(function($qry) use ($param_date){
                                $qry->whereNull('issuance_date')->orWhereNull('expiration_date_document');
                            })->where('seller_id', $this->user_login_id);
                        });
                    } else if(isset($param_date['start_date'])) {
                        $query->where(function($qry) use ($param_date){
                            $qry->where(function($qry) use ($param_date){
                                $qry->whereDate('issuance_date', '<=', $param_date['start_date'])->orWhereNull('expiration_date_document');
                            })->orWhere(function($qry) use ($param_date){
                                $qry->whereNull('issuance_date')->orWhereNull('expiration_date_document');
                            })->where('seller_id', $this->user_login_id);
                        });
                    }
                }
            })
            ->when(isset($param['search']), function ($querys) use ($param) {
                $querys->where(function ($query) use ($param) {
                    $query->where(function ($qry) use ($param) {
                        $qry->where('amount', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('preferred_payment_method', 'like', '%' . $param['search'] . '%')
                            ->orWhere('check_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('issuer_company_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_type', 'like', '%' . $param['search'] . '%');
                    })->orWhere(function ($query) use ($param) {
                        $query->whereHas('issuer', function ($qry) use($param) {
                            $qry->where('company_name', 'like', '%' . $param['search'] . '%');
                        })->orWhereHas('issuer_bank', function ($qry) use($param) {
                            $qry->where('name', 'like', '%' . $param['search'] . '%');
                        });
                    });
                });
            })
            ->when(isset($param['offer_status']), function ($qry) use ($param) {
                if (isset($param['offer_status']) && $param['offer_status'] == 'Sold') {
                    $qry->whereHas('offers', function ($qr) {
                        $qr->where('offer_status', 'Approved');
                    });
                } else if (isset($param['offer_status']) && $param['offer_status'] == 'Unsold') {
                    $qry->whereHas('offers', function ($qr) {
                        $qr->where('offer_status', '!=', 'Approved');
                    });
                } else if (isset($param['offer_status']) && $param['offer_status'] == 'Counter') {
                    $qry->whereHas('offers', function ($qr) {
                        $qr->where('offer_status', 'Counter');
                    });
                }
            })
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

    public function getOperationDashboardWeb($param, $pagination = false)
    {
        return Operation::select('operations.id', 'operations.slug','operations.operation_id', 'operations.seller_id',
            'operations.issuer_id', 'operations.issuer_bank_id', 'operations.mipo_verified',
            'operations.operation_type', 'operations.preferred_currency', 'operations.responsibility', 'operations.preferred_payment_method', 'operations.operations_status', 'operations.expiration_date', 'operations.extra_expiration_days')
            ->with([
            'offers',
            ])->when($param, function ($qry) use ($param) {
                if(isset($param['seller_id']) && !empty($param['seller_id']) > 0) {
                    $qry->where('seller_id', $param['seller_id']);
                } else {
                    $qry->where('seller_id', $this->user_login_id);
                }
            })
            ->when($param, function ($qry) use ($param) {
                if (!empty($param['operation_type'])) {
                    $qry->whereIn('operation_type', $param['operation_type']);
                }
                if (!empty($param['preferred_currency'])) {
                    $qry->whereIn('preferred_currency', $param['preferred_currency']);
                }
                if (!empty($param['responsibility'])) {
                    $qry->whereIn('responsibility', $param['responsibility']);
                }
                if (!empty($param['preferred_payment_method'])) {
                    $qry->whereIn('preferred_payment_method', $param['preferred_payment_method']);
                }
                if (!empty($param['operation_status'])) {
                    if($param['operation_status'] == 'Unsold') {
                        $qry->where('operations_status', '!=', 'Approved')->whereDate('expiration_date', '<', date('Y-m-d'));
                    } else {
                        $qry->where('operations_status', $param['operation_status']);
                    }
                }
                if (isset($param['mipo_verified']) && $param['mipo_verified'] == 'Yes') {
                    $qry->where('mipo_verified', 'Yes');
                }
                if (!empty($param['add_tags'])) {
                    $qry->when($param, function ($qry) use ($param) {
                        $qry->whereHas('tags', function ($q) use ($param) {
                            $q->whereIn('id', $param['add_tags']);
                        });
                    });
                }
            })
            ->when(isset($param['search']), function ($querys) use ($param) {
                $querys->where(function ($query) use ($param) {
                    $query->where(function ($qry) use ($param) {
                        $qry->where('amount', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('operation_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('preferred_payment_method', 'like', '%' . $param['search'] . '%')
                            ->orWhere('check_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('issuer_company_type', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_number', 'like', '%' . $param['search'] . '%')
                            ->orWhere('invoice_type', 'like', '%' . $param['search'] . '%');
                    })->orWhere(function ($query) use ($param) {
                        $query->whereHas('issuer', function ($qry) use($param) {
                            $qry->where('company_name', 'like', '%' . $param['search'] . '%');
                        })->orWhereHas('issuer_bank', function ($qry) use($param) {
                            $qry->where('name', 'like', '%' . $param['search'] . '%');
                        });
                    });
                });
            })
            ->when(isset($param['offer_status']), function ($qry) use ($param) {
                if (isset($param['offer_status']) && $param['offer_status'] == 'Sold') {
                    $qry->whereHas('offers', function ($qr) {
                        $qr->where('offer_status', 'Approved');
                    });
                } else if (isset($param['offer_status']) && $param['offer_status'] == 'Unsold') {
                    $qry->whereHas('offers', function ($qr) {
                        $qr->where('offer_status', '!=', 'Approved');
                    });
                }else if (isset($param['offer_status']) && $param['offer_status'] == 'Counter') {
                    $qry->whereHas('offers', function ($qr) {
                        $qr->where('offer_status', 'Counter');
                    });
                }
            })->get();
    }

    public function delete($slug, $seller_id = null)
    {
        return Operation::doesntHave('offers')->withTrashed()
            ->when($slug, function($qry) use ($slug){
                if($slug!=''){
                    $qry->where('slug', $slug);
                }
            })
            ->when($seller_id > 0, function($qry) use ($seller_id) {
                $qry->where('seller_id', $seller_id);
            })->where('operations_status', '!=', 'Approved')->delete();
    }

    public function forceDelete($slug, $seller_id = null)
    {
        return Operation::doesntHave('offers')->withTrashed()
            ->when($slug, function($qry) use ($slug){
                if($slug!=''){
                    $qry->where('slug', $slug);
                }
            })
            ->when($seller_id > 0, function($qry) use ($seller_id) {
                $qry->where('seller_id', $seller_id);
            })->where('operations_status', '!=', 'Approved')->forceDelete();
    }

    public function restore($seller_id)
    {
        return Operation::where('seller_id', $seller_id)->withTrashed()->update(['deleted_at' => null]);
    }

    public function deleteAttachments($slug)
    {
        $supportingAttachment = SupportingAttachment::where('slug', $slug)->first();
        if ($supportingAttachment) {
            $imagePath = storage_path('app/' . $supportingAttachment->path);
            if (file_exists($imagePath)) {
                Storage::delete($supportingAttachment->path);
            }
            return $supportingAttachment->delete();
        } else {
            return false;
        }
    }

    public function deleteDocuments($slug)
    {
        $document = Document::where('slug', $slug)->first();
        if ($document) {
            $imagePath = storage_path('app/' . $document->path);
            if (file_exists($imagePath)) {
                Storage::delete($document->path);
            }
            return $document->delete();
        } else {
            return false;
        }
    }

    public function getPichartData()
    {
        $pichart_qry =  Operation::select('operation_type','id', 'preferred_currency',
            DB::raw('count(operation_type) as operation_type_total'),
            DB::raw('count(id) as id_total'),
            DB::raw('count(preferred_currency) as preferred_currency_total') 
            )
            ->whereHas('offers', function($qry){
                $qry->where('offer_status', 'Approved');
            })
            ->with('offers')
            ->where('seller_id', Auth()->user()->id)
            ->groupBy('operation_type')->groupBy('id')->groupBy('preferred_currency')
            ->get();
        
            return array('data' => [
                $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_name)->count(),
                $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->count(),
                $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_sign)->count(),
                $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->count(),
                $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_sign)->count(),
                $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->count(),
                $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_sign)->count(),
                $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->count(),
                ],
                'labels' => [
                __('Invoice') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->dollar_sign)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                __('Invoice') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Invoice')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                __('Check'). $this->dollar_sign. $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->dollar_sign)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                __('Check'). $this->pyg_sign.  $pichart_qry->where('operation_type', 'Cheque')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                __('Contract') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->dollar_sign)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                __('Contract') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Contract')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                __('Other') .$this->dollar_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->dollar_sign)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                __('Other') .$this->pyg_sign. $pichart_qry->where('operation_type', 'Other')->where('preferred_currency', $this->pyg_name)->flatten('offers')->pluck('offers')->flatten()->pluck('amount')->sum().'k',
                ],
            );
    }


    public function getOperationById($operation_id = null, $operation_slug = null) 
    {
        return Operation::OperationSelect()
        ->with([
            'seller' => function ($qry) {
                $qry->select('id', 'name','account_type', 'user_level', 'security_level', 'phone_number', 'city_id', 'profile_image', 'birth_date', 'registered_at', 'preferred_currency');
                $qry->with('city:id,name')->withAvg('ratings', 'rating_number')
                ->with('issuer', function ($qry) {
                    $qry->select('id','company_name', 'slug', 'ruc_text_id', 'bcp', 'inforconf', 'infocheck', 'criterium', 'registry_in_mipo', 'verified_address');
                });
            },
            'issuer' => function ($qry) {
                $qry->select('id','company_name', 'slug', 'ruc_text_id', 'bcp', 'inforconf', 'infocheck', 'criterium', 'registry_in_mipo', 'verified_address')->withAvg('ratings', 'rating_number');
            },
            'issuer_bank' => function ($qry) {
                $qry->select('id','name', 'slug');
            },
            'references',
            'documents' => fn($qry) => $qry->select('id', 'operation_id', 'name', 'display_name', 'path'),
            'supportingAttachments' => fn($qry) => $qry->select('id', 'operation_id', 'name', 'display_name', 'path'),
        ])
        ->when($operation_id, fn($qry) => $qry->where('id', $operation_id))
        ->when($operation_slug, fn($qry) => $qry->where('slug', $operation_slug))
        ->first();
    }

    public function getOperationByIdWithSeller($operation_id) 
    {
        return Operation::where('id', $operation_id)
            ->select('id','operation_number', 'seller_id')->with('seller:id,name,email')->first();
    }

    public function getOperationByIdsWithSeller($operation_ids) 
    {
        return Operation::whereIn('id', $operation_ids)
            ->select('id','operation_number', 'seller_id')
            ->with('seller:id,name,email')->get();
    }

    public function deleteProcessStatusFile($id, $clm_name)
    {
        $is_result = OperationProgressStatusFile::where('id', $id)->first();
        if ($is_result) {
            if($clm_name == 'bcp') {
                app('common')->fileDeleteFromFolder($is_result->bcp_file);
                $is_result->bcp_file = null;
            } else if($clm_name == 'inforconf') {
                app('common')->fileDeleteFromFolder($is_result->inforconf_file);
                $is_result->inforconf_file = null;
            }
            else if($clm_name == 'infocheck') {
                app('common')->fileDeleteFromFolder($is_result->infocheck_file);
                $is_result->infocheck_file = null;
            }
            else if($clm_name == 'criterium') {
                app('common')->fileDeleteFromFolder($is_result->criterium_file);
                $is_result->criterium_file = null;
            }
            return $is_result->save();
        } else {
            return false;
        }
    }

    public function deleteAdminStaffAttachmentsFile($id)
    {
        $is_result = OperationsAdminStaffFile::where('id', $id)->first();
        if ($is_result) {
            $imagePath = storage_path('app/' . $is_result->path);
            if (file_exists($imagePath)) {
                Storage::delete($is_result->path);
            }
            return $is_result->delete();
        } else {
            return false;
        }
    }

    public function totalOperationByUser($seller_id)
    {
        return Operation::where('seller_id', $seller_id)->count();
    }

}
?>
