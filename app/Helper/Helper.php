<?php 

namespace App\Helper;
use Illuminate\Support\Facades\Storage;
use App\Models\OperationsLogs;
use App\Models\User;
use App\Models\Rating;
use App\Models\Offer;
use App\Models\UserLevel;
use App\Models\Issuer;
use App\Models\Operation;
use App\Models\Settings;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OfferFirstAccepted as NotificationsOfferFirstAccepted;
use App\Notifications\OfferOtherAccepted as NotificationsOfferOtherAccepted;
use App\Notifications\OfferRankUpAccepted as NotificationsOfferRankUpAccepted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Blog;

class Helper
{
    public function currencyNumberFormat($currency, $amount)
    {
        if($currency == 'USD' && !empty($amount))
        {
            return number_format($amount,2,',', '.');
        } else if(in_array($currency, ['PYGH', 'PYG','GS', 'GS.', 'Gs', 'Gs.']) && !empty($amount))
        {
            return number_format($amount,0,',', '.');
        } else {
            if(!empty($amount)) {
                return number_format($amount);
            } else {
                return '0';
            }
        }
    }

    public function fileDeleteFromFolder($file_path)
    {
        if(!empty($file_path))
        {
            $file_full_path = storage_path('app/' . $file_path);
            if (file_exists($file_full_path)) {
                Storage::delete($file_path);
                // @unlink($file_full_path);
            }
        }
    }

    public function statusByColor($status)
    {
        $class = '';
        if($status == 'Pending')
        {
            $class = 'text-primary';
        } else if($status == 'Counter')
        {
            $class = 'text-warning';
            
        } else if($status == 'Approved')
        {
            $class = 'text-success';
            
        } else if($status == 'Rejected'){
            $class = 'text-danger';
        }
        return $class;
    }

    public function statusByBgColor($status)
    {
        $class = '';
        if($status == 'Pending')
        {
            $class = 'bg-primary';
        } else if($status == 'Counter')
        {
            $class = 'bg-warning';
            
        } else if($status == 'Approved')
        {
            $class = 'bg-success';
            
        } else if($status == 'Rejected'){
            $class = 'bg-danger';
        }
        return $class;
    }

    public function currencyBySign($currency)
    {
        $class = 'gs';
        if($currency == 'USD')
        {
            $class = 'dollar';
        }

        return $class;
    }

    public function dealsTracking($title, $operation_id = 0 , $offer_id = 0, $log_type = 'All')
    {
        $calss = '';
        $result =  OperationsLogs::when($log_type, function($qry) use ($offer_id, $operation_id) {
                if($offer_id > 0) {
                    $qry->where('offer_id', $offer_id);
                } else {
                    $qry->where('operation_id', $operation_id);
                }
        })->where('title', trim($title))->where('log_types', $log_type)->first();
        if($result)
        {
            if($result->is_current == '1') {
                $calss= 'current';
            } else if($result->is_completed == '1' && $result->is_current == '0') { 
                $calss= 'filled';
            }
        }
        return $calss;
    }

    public function dealsTracking_new($title, $operation_id = 0 , $offer_id = 0, $log_type = 'All')
    {
        $return_data = array('class' => 'pending', 'data_time' => '');
        $result =  OperationsLogs::when($log_type, function($qry) use ($offer_id, $operation_id) {
                if($offer_id > 0) {
                    $qry->where('offer_id', $offer_id);
                } else {
                    $qry->where('operation_id', $operation_id);
                }
        })->where('title', trim($title))->where('log_types', $log_type)->first();
        
        if($result)
        {
            // \Log::info($result->title);
            if($result->is_current == '1') {
                $return_data = ['class' =>'current', 'data_time' => $result->log_date_time_iso];
            } else if($result->is_completed == '1' && $result->is_current == '0') { 
                $return_data = ['class' => 'complete', 'data_time' => $result->log_date_time_iso];
            } else if($result->is_completed == '1') { 
                $return_data = ['class' => 'complete', 'data_time' => $result->log_date_time_iso];
            } else {
                $return_data = ['class' => 'pending', 'data_time' => ''];

            }
        }
        return $return_data;
    }

    public function operationChequeStatus($cheque_status)
    {
        if($cheque_status == 'Todate') {
            return asset('images/mipo/to-date-cheque.svg');
        } else if($cheque_status == 'Postponed') {
            return asset('images/mipo/posponed-cheque.svg');
        }
    }

    public function operationChequeType($cheque_type)
    {
        if($cheque_type == 'Crossed') {
            return asset('images/mipo/crossed-cheque.svg');
        } else if($cheque_type == 'Open') {
            return asset('images/mipo/payable.svg');
        }
    }

    public function operationChequePayeeType($cheque_payee_type)
    {
        if($cheque_payee_type == 'Anyone') {
            return asset('images/mipo/payable-to-anyone.svg');
        } else if($cheque_payee_type == 'Named') {
            return asset('images/mipo/named-payee.svg');
        }
    }

    public function userAccountType($account_type)
    {
        if($account_type == 'Enterprise') {
            return asset('images/mipo/users.svg');
        } else if($account_type == 'Individual') {
            return asset('images/mipo/user.svg');
        }
    }


    public function userLevelImage($user_level_name)
    {
        if($user_level_name  == 'Noobie') {
            return asset('images/mipo/noobie.svg');
        } else if($user_level_name == 'Bronze') {
            return asset('images/mipo/bronze.svg');
        } else if($user_level_name == 'Silver') {
            return asset('images/mipo/silver.svg');
        } else if($user_level_name == 'Gold') {
            return asset('images/mipo/gold.svg');
        } else if($user_level_name == 'Platinum') {
            return asset('images/mipo/platinum.svg');
        }
    }
    
    public function issuerRatingImage($issuer_rating_avg)
    {
        if($issuer_rating_avg < 0.5) {
            return asset('images/mipo/rating/0.svg');
        } else if($issuer_rating_avg < 1) {
            return asset('images/mipo/rating/0.5.svg');
        }  else if($issuer_rating_avg < 1.5) {
            return asset('images/mipo/rating/1.svg');
        } else if($issuer_rating_avg < 2) {
            return asset('images/mipo/rating/1.5.svg');
        } else if($issuer_rating_avg < 2.5) {
            return asset('images/mipo/rating/2.svg');
        } else if($issuer_rating_avg < 3) {
            return asset('images/mipo/rating/2.5.svg');
        } else if($issuer_rating_avg < 3.5) {
            return asset('images/mipo/rating/3.svg');
        } else if($issuer_rating_avg < 4) {
            return asset('images/mipo/rating/3.5.svg');
        } else if($issuer_rating_avg < 4.5) {
            return asset('images/mipo/rating/4.svg');
        } else if($issuer_rating_avg < 5) {
            return asset('images/mipo/rating/4.5.svg');
        } else {
            return asset('images/mipo/rating/5.svg');
        }
    }

    public function userRatingImage($user_rating_avg)
    {
        if($user_rating_avg < 0.5) {
            return asset('images/mipo/rating/0.svg');
        } else if($user_rating_avg < 1) {
            return asset('images/mipo/rating/0.5.svg');
        }  else if($user_rating_avg < 1.5) {
            return asset('images/mipo/rating/1.svg');
        } else if($user_rating_avg < 2) {
            return asset('images/mipo/rating/1.5.svg');
        } else if($user_rating_avg < 2.5) {
            return asset('images/mipo/rating/2.svg');
        } else if($user_rating_avg < 3) {
            return asset('images/mipo/rating/2.5.svg');
        } else if($user_rating_avg < 3.5) {
            return asset('images/mipo/rating/3.svg');
        } else if($user_rating_avg < 4) {
            return asset('images/mipo/rating/3.5.svg');
        } else if($user_rating_avg < 4.5) {
            return asset('images/mipo/rating/4.svg');
        } else if($user_rating_avg < 5) {
            return asset('images/mipo/rating/4.5.svg');
        } else {
            return asset('images/mipo/rating/5.svg');
        }
    }
    /* calculationForOffer() date 16/2/2023  */
    public function calculationForOffer($request_param, $action = 'add')
    { 
        $group_operation = $single_operation = [];
        
        $investor_commission = app('common')->userPlan()->investor_commission;
        $mipo_commission = app('common')->userPlan()->mipo_commission;

        $total_retention = $total_offer_amount = $total_commsion = $total_mipo_commsion = $total_net_profit = 0;
        
        if(isset($request_param['offer_type']) && $request_param['offer_type'] == 'Group')
        {
            if($action ==='update_offer' && isset($request_param['operation_id'])) {
                $operation_amount_req = 0;
                $operation_amount_req = Operation::select('amount')->whereIn('id', $request_param['operation_id'])->sum('amount');
                $retention = $request_param['retention'] ?? 0;
                $offer_amount = $request_param['offer_amount'] ?? 0;
                $is_mipo_plus = $request_param['is_mipo_plus'] ?? 'false';

                $current_operation_amount = ($operation_amount_req - $retention);

                $current_overall_profit = ($current_operation_amount - $offer_amount);

                $current_mipo_commission = (($current_overall_profit * $investor_commission) / 100);

                if(isset($is_mipo_plus) && $is_mipo_plus == 'true') {
                    $current_add_mipo_commission = (($offer_amount * $mipo_commission) / 100);
                } else if(isset($is_mipo_plus) && $is_mipo_plus == 'false') {
                    $current_add_mipo_commission = 0;
                } else {
                    $current_add_mipo_commission = 0;
                }

                $current_net_profit = ($current_overall_profit - $current_mipo_commission - $current_add_mipo_commission);

                $total_retention += $retention;
                $total_offer_amount += $offer_amount;
                $total_commsion += $current_mipo_commission;
                $total_mipo_commsion += $current_add_mipo_commission;
                $total_net_profit += $current_net_profit;

            } else if($action ==='add' && isset($request_param['operation_id'])) {
                $operation_amount_req = 0;
                $operation_amount_req = Operation::select('amount')->whereIn('id', $request_param['operation_id'])->sum('amount');
                $retention = $request_param['retention'] ?? 0;
                $offer_amount = $request_param['offer_amount'] ?? 0;
                $is_mipo_plus = $request_param['is_mipo_plus'] ?? 'false';
                $current_operation_amount = ($operation_amount_req - $retention);

                $current_overall_profit = ($current_operation_amount - $offer_amount);

                $current_mipo_commission = (($current_overall_profit * $investor_commission) / 100);

                if(isset($is_mipo_plus) && $is_mipo_plus == 'true') {
                    $current_add_mipo_commission = (($offer_amount * $mipo_commission) / 100);
                } else if(isset($is_mipo_plus) && $is_mipo_plus == 'false') {
                    $current_add_mipo_commission = 0;
                } else {
                    $current_add_mipo_commission = 0;
                }

                $current_net_profit = ($current_overall_profit - $current_mipo_commission - $current_add_mipo_commission);

                $total_retention += $retention;
                $total_offer_amount += $offer_amount;
                $total_commsion += $current_mipo_commission;
                $total_mipo_commsion += $current_add_mipo_commission;
                $total_net_profit += $current_net_profit;
            }
            $is_mipo_plus = $request_param['is_mipo_plus'] ?? 'false';
        } else {
        
            $operaion_id = $request_param['operation_id'];
            $seller_id = $request_param['seller_id'] ?? '';
            $retention = $request_param['retention'] ?? 0;
            $offer_amount = $request_param['offer_amount'] ?? 0;
            $is_mipo_plus = $request_param['is_mipo_plus'] ?? 'false';
            
            $operation_amount_req = 0;

            if($action == 'update_offer') {
                $operation_result = Operation::select('id','amount')->whereIn('id', $operaion_id)->first();
            } else {
                $operation_result = Operation::select('id','amount')->where('id', $operaion_id)->where('seller_id', $seller_id)->first();
            }
            
            if($operation_result && !empty($operation_result->amount)) 
            {
                $operation_amount_req = $operation_result->amount;

                $current_operation_amount = ($operation_amount_req - $retention);

                $current_overall_profit = ($current_operation_amount - $offer_amount);

                $current_mipo_commission = (($current_overall_profit * $investor_commission) / 100);

                if(isset($is_mipo_plus) && $is_mipo_plus == 'true') {
                    $current_add_mipo_commission = (($offer_amount * $mipo_commission) / 100);
                } else if(isset($is_mipo_plus) && $is_mipo_plus == 'false') {
                    $current_add_mipo_commission = 0;
                } else {
                    $current_add_mipo_commission = 0;
                }

                $current_net_profit = ($current_overall_profit - $current_mipo_commission - $current_add_mipo_commission);

                $total_retention += $retention;
                $total_offer_amount += $offer_amount;
                $total_commsion += $current_mipo_commission;
                $total_mipo_commsion += $current_add_mipo_commission;
                $total_net_profit += $current_net_profit;
            }
        }

        $mipo_verify = 'No';
        if(isset($is_mipo_plus) && $is_mipo_plus == 'true') {
            $mipo_verify = 'Yes';
        }

        return array(
            'retention' => round($total_retention, 2),
            'mipo_commission' => round($total_commsion, 2),
            'mipo_plus_commission' => round($total_mipo_commsion, 2),
            'net_profit' => round($total_net_profit, 2),
            'is_mipo_plus' => $mipo_verify
        );
    }

     /* calculationForOffer() old_working  */
    public function calculationForOffer_old_working($request_param, $action = 'add')
    { 
    
        $group_operation = $single_operation = [];

        $total_retention = $total_offer_amount = $total_commsion = $total_mipo_commsion = $total_net_profit = 0;
        
        if(isset($request_param['offer_type']) && $request_param['offer_type'] == 'Group')
        {
            if($action ==='update_offer' && isset($request_param['operation_id'])) {

                $operation_amount_req = 0;
                $operation_amount_req = Operation::select('amount')->whereIn('id', $request_param['operation_id'])->sum('amount');
                $retention = $request_param['retention'] ?? 0;
                $offer_amount = $request_param['offer_amount'] ?? 0;
                $is_mipo_plus = $request_param['is_mipo_plus'] ?? 'false';

                $current_operation_amount = ($operation_amount_req - $retention);

                $current_overall_profit = ($current_operation_amount - $offer_amount);

                $current_mipo_commission = (($current_overall_profit * config('constants.MIPO_COMMISSION')) / 100);

                if(isset($is_mipo_plus) && $is_mipo_plus == 'true') {
                    $current_add_mipo_commission = (($offer_amount * config('constants.MIPO_ADD_COMMISSION')) / 100);
                } else if(isset($is_mipo_plus) && $is_mipo_plus == 'false') {
                    $current_add_mipo_commission = 0;
                } else {
                    $current_add_mipo_commission = 0;
                }

                $current_net_profit = ($current_overall_profit - $current_mipo_commission - $current_add_mipo_commission);

                $total_retention += $retention;
                $total_offer_amount += $offer_amount;
                $total_commsion += $current_mipo_commission;
                $total_mipo_commsion += $current_add_mipo_commission;
                $total_net_profit += $current_net_profit;
                    
            } else {
                if(isset($request_param['operation_form']) && $request_param['operation_form'])
                {
                    foreach($request_param['operation_form'] as $key => $form_val) 
                    {
                        $operaion_id = $form_val['operaion_id'];
                        $seller_id = $form_val['seller_id'];
                        $retention = $form_val['operaion_retention'] ?? 0;
                        $offer_amount = $form_val['operaion_offer_amount'] ?? 0;
                        $operation_amount_req = 0;

                        $operation_result = Operation::select('id','amount')->where('id', $operaion_id)->where('seller_id', $seller_id)->first();

                        if($operation_result && !empty($operation_result->amount)) 
                        {
                            $operation_amount_req = $operation_result->amount;

                            $current_operation_amount = ($operation_amount_req - $retention);

                            $current_overall_profit = ($current_operation_amount - $offer_amount);

                            $current_mipo_commission = (($current_overall_profit * config('constants.MIPO_COMMISSION')) / 100);

                            if(isset($form_val['operaion_mipo_plus']) && $form_val['operaion_mipo_plus'] == 'Yes') {
                                $current_add_mipo_commission = (($offer_amount * config('constants.MIPO_ADD_COMMISSION')) / 100);
                            } else if(isset($form_val['operaion_mipo_plus']) && $form_val['operaion_mipo_plus'] == 'No') {
                                $current_add_mipo_commission = 0;
                            } else {
                                $current_add_mipo_commission = 0;
                            }
                            
                            $current_net_profit = ($current_overall_profit - $current_mipo_commission - $current_add_mipo_commission);

                            $total_retention += $retention;
                            $total_offer_amount += $offer_amount;
                            $total_commsion += $current_mipo_commission;
                            $total_mipo_commsion += $current_add_mipo_commission;
                            $total_net_profit += $current_net_profit;
                        }
                    }
                }
            }
            $is_mipo_plus = $request_param['is_mipo_plus'] ?? 'false';
        } else {
            $operaion_id = $request_param['operation_id'];
            $seller_id = $request_param['seller_id'] ?? '';
            $retention = $request_param['retention'] ?? 0;
            $offer_amount = $request_param['offer_amount'] ?? 0;
            $is_mipo_plus = $request_param['is_mipo_plus'] ?? 'false';
            $operation_amount_req = 0;

            if($action == 'update_offer') {
                $operation_result = Operation::select('id','amount')->whereIn('id', $operaion_id)->first();
            } else {
                $operation_result = Operation::select('id','amount')->where('id', $operaion_id)->where('seller_id', $seller_id)->first();
            }
            
            if($operation_result && !empty($operation_result->amount)) 
            {
                $operation_amount_req = $operation_result->amount;

                $current_operation_amount = ($operation_amount_req - $retention);

                $current_overall_profit = ($current_operation_amount - $offer_amount);

                $current_mipo_commission = (($current_overall_profit * config('constants.MIPO_COMMISSION')) / 100);

                if(isset($is_mipo_plus) && $is_mipo_plus == 'true') {
                    $current_add_mipo_commission = (($offer_amount * config('constants.MIPO_ADD_COMMISSION')) / 100);
                } else if(isset($is_mipo_plus) && $is_mipo_plus == 'false') {
                    $current_add_mipo_commission = 0;
                } else {
                    $current_add_mipo_commission = 0;
                }

                $current_net_profit = ($current_overall_profit - $current_mipo_commission - $current_add_mipo_commission);

                $total_retention += $retention;
                $total_offer_amount += $offer_amount;
                $total_commsion += $current_mipo_commission;
                $total_mipo_commsion += $current_add_mipo_commission;
                $total_net_profit += $current_net_profit;
            }
        }

        $mipo_verify = 'No';
        if(isset($is_mipo_plus) && $is_mipo_plus == 'true') {
            $mipo_verify = 'Yes';
        }

        return array(
            'retention' => round($total_retention, 2),
            'mipo_commission' => round($total_commsion, 2),
            'mipo_plus_commission' => round($total_mipo_commsion, 2),
            'net_profit' => round($total_net_profit, 2),
            'is_mipo_plus' => $mipo_verify
        );
    }

    public function updateUserLevel($offer_id)
    {
       $result = Offer::select('id', 'offer_status')->with('operations:id,seller_id')
            ->where('id', $offer_id)
            ->where('offer_status', 'Approved')->first();
       
        $user_id = $result->operations->first()->seller_id;
      
       $total_user_offer = Offer::select('id', 'offer_status')->whereHas('operations', function($qry) use ($user_id) {
                $qry->where('seller_id', $user_id);
            })->where('offer_status', 'Approved')->count();
       
        if($total_user_offer == 0) {
            $user_level_name = 'Noobie';
        } else if($total_user_offer >= 1) {
            $user_level_name = 'Bronze';
        } else if($total_user_offer >= 11) {
            $user_level_name = 'Silver';
        } else if($total_user_offer >= 26) {
            $user_level_name = 'Gold';
        } else if($total_user_offer >= 51) {
            $user_level_name = 'Platinum';
        }

        User::where('id', $user_id)->update(['user_level' => $user_level_name]);
       
        $user_obj = User::where('id', $user_id)->select('id', 'name', 'email', 'user_level')->first();
       
        try {
            $notifications_operation = config('constants.NOTIFICATIONS_TYPES.OFFERS');

            if($notifications_operation['Approved']) {
                // Bronze member
                Notification::send($user_obj, new NotificationsOfferFirstAccepted($user_obj->user_level));
            }

            if($notifications_operation['Approved']) {
                 // comming soon as Silver member
                Notification::send($user_obj, new NotificationsOfferOtherAccepted($user_obj->user_level));
            }

            if($notifications_operation['Approved']) {
                //Silver member
                Notification::send($user_obj, new NotificationsOfferRankUpAccepted($user_obj->user_level));
           }
           
        } catch (\Throwable $th) {
            \Log::info($th);
        }
    }

    public function removeSpecialChars($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public function becomingUserLevel($user_level_name)
    {
        $user_levels_data =  UserLevel::select('name', 'number_of_deals')->get()->toArray();
        $current_level = 0;
        $next_level_details = [];
        foreach($user_levels_data as $key => $user_levels) {
            if($user_levels['name']  == $user_level_name){
                $current_level = $key;
            }
        }
        
        $next_level_details = $user_levels_data[$current_level+1];
        $total_user_offer = Offer::select('id', 'offer_status')->whereHas('operations', function($qry) {
            $qry->where('seller_id',  Auth()->user()->id);
        })->where('offer_status', 'Approved')->count();
        
        return(
            [
                'next_user_level_name' => strtolower($next_level_details['name']),
                'complete_deals' => ($next_level_details['number_of_deals'] - $total_user_offer),
                'total_next_level' => $next_level_details['number_of_deals']
            ]);
    }

    public function currencyBySymbol($currency)
    {
        if($currency == 'USD')
        {
            return '$';
        } else if(in_array($currency, ['PYGH', 'PYG','GS', 'GS.', 'Gs', 'Gs.']) && !empty($currency))
        {
            return '₲';
        } else {
            return '$';
        }
    }

    public function dateRangeExplode($date_range, $explode_sign)
    {
        $arr_date_range = explode($explode_sign, trim($date_range));

        if(isset($arr_date_range[0]) && !empty($arr_date_range[0])) {
            $start_date = trim($arr_date_range[0]);
            $res['start_date'] = Carbon::createFromFormat('d/m/Y', $start_date)->format('Y-m-d');
        }
        if(isset($arr_date_range[1]) && !empty($arr_date_range[1])) {
            $end_date = trim($arr_date_range[1]);
            $res['end_date'] = Carbon::createFromFormat('d/m/Y', $end_date)->format('Y-m-d');
        }
        return  $res;
    }

    public function operationStatusBgcolor($operations_status)
    {   
        $bg_color = '';
        if ($operations_status == 'Draft') {
            return $bg_color = 'dark' ;
        } else if ($operations_status == 'Pending') {
            return $bg_color = 'primary' ;
        } else if ($operations_status == 'Rejected') {
            return $bg_color = 'danger' ;
        } else if ($operations_status == 'Approved') {
            return $bg_color = 'success' ;
        }
    }

    public function lockOperationDetail($operation_obj, $lock_param = [])
    {
        if($lock_param && in_array('seller_name', $lock_param)) {
            return $operation_obj->seller?->name;
            // return substr($operation_obj->seller?->name,0, 1) .'***';
        } else if($lock_param && in_array('seller_ruc', $lock_param)) {
            return $operation_obj->seller?->issuer->ruc_code;
            // return substr($operation_obj->seller?->issuer->ruc_code,0, 1) .'***';
        } else if($lock_param && in_array('payer_name', $lock_param)) {
            return substr($operation_obj->seller?->issuer->company_name,0, 1) .'***';
        } else if($lock_param && in_array('payer_ruc', $lock_param)) {
            return substr($operation_obj->seller?->issuer->ruc_code,0, 1) .'***';
        } else {
            return $operation_obj->seller?->name;
        }
    }

    public function lockOfferDetail($offer_obj, $lock_param = [])
    {
        if($lock_param && in_array('buyer_name', $lock_param)) {
            return substr( $offer_obj->buyer->name,0, 1) .'***';
        } else {
            return $offer_obj->buyer->name;
        }
    }

    public function lockUserDetail($user_obj, $lock_param = [])
    {
        if($lock_param && in_array('user_name', $lock_param)) {
            return substr($user_obj->name,0, 1) .'***';
        } else {
            return $user_obj->name;
        }
    }

    public function otpGenerate()
    {
        return rand(123456, 999999);
    }

    public function randomPasswordGenerate($length = 8)
    {
        return Str::random($length);
    }

    /* used for this function
        $emails_cc = app('common')->sendEmailCC();
        $emails_bcc = app('common')->sendEmailBCC(); 
    */
    
    public function sendEmailCC()
    {
        $emails_cc = false;
        $cc = config('constants.CC');
        $is_send_cc = $cc['SEND'];
        if($is_send_cc) {
            $emails_cc = collect($cc['EMAILS'])->where('send', true)->pluck('email')->toArray();
        }
        return $emails_cc;
    }

    public function sendEmailBCC()
    {
        $emails_bcc = false;
        $bcc = config('constants.BCC');
        $is_send_bcc = $bcc['SEND'];
        if($is_send_bcc) {
            $emails_bcc = collect($bcc['EMAILS'])->where('send', true)->pluck('email')->toArray();
        }
        return $emails_bcc;
    }

    public function timeHumanReadableFormat($dateTime)
    {
        $get_value = Carbon::createFromDate($dateTime)->diffForHumans();
        return str_replace('from now', '',  $get_value);
    }

    public function tfImage($condition)
    {
        if($condition == 'yes'){
            $imgFilename = 'checkmark.svg';
        }else{
            $imgFilename = 'close.svg';
        }
        return asset('images/mipo/'.$imgFilename);
    }
    
    public function getSettingsVal()
    {
        return Settings::orderBy('id', 'desc')->first();
    }

    public function getUserIP()
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }

    public function getUserDevice()
    {
        return Request()->header('User-Agent');
    }

    public function addLogs($title, $user_id = null)
    {
        $save_update = new OperationsLogs;
        $save_update->is_completed = 0;
        $save_update->is_current = 0;
        $save_update->operation_id = null;
        $save_update->offer_id = null;
        $save_update->title = $title;
        $save_update->log_types = 'All';
        $save_update->completed_at = Carbon::now();
        $save_update->user_ip_address = $this->getUserIP();
        $save_update->user_device = $this->getUserDevice();
        $save_update->user_id = $user_id ?? Auth()->user()?->id;
        return $save_update->save();
    }

    public function getReferrerCode ($length_of_string = 5)
    {
        $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz'; 
        return substr(str_shuffle($str_result), 0, $length_of_string).Auth()->user()?->id; 
    }

    public function generateInvitationToken()
    {
        return substr(md5(rand(0, 9) . Auth()->user()->email  . time()), 0, 32);
    }

    public function getUserEmail($user_id = null)
    {
        if($user_id > 0) {
            $user_id = $user_id; 
        } else {
            $user_id = Auth()->user()->id; 
        }
        return User::where('id', $user_id)->select('id', 'name', 'email', 'account_type', 'as_borrower', 'as_investor', 'is_registered', 'is_active', 'address_verify', 'user_level')->first();
    }

    public function getUserDetailsRoleBy($role_id = 1)
    {
        if($role_id > 0) {
            $role_id = $role_id; 
        } else {
            $role_id = Auth()->user()->is_admin; 
        }
        return User::where('is_admin', $role_id)->select('id', 'name', 'email', 'account_type', 'as_borrower', 'as_investor', 'is_registered', 'is_active', 'address_verify', 'user_level')->get();
    }

    public function getProfitLoss($invested_amount = 0, $profit_amount = 0)
    {
        (int) $invested_amount = $invested_amount;
        (int) $profit_amount = $profit_amount;
        
        $per = 0;
        
        if($invested_amount > 0 && $profit_amount > 0) {
            $per = ($invested_amount / $profit_amount);
        }

        return round($per);
    }

    public function lockOfferBy($name, $lock_param = [])
    {
        if($lock_param && in_array('offer_by', $lock_param)) {
            return substr($name,0, 1) .'***';
        } else {
            return $name;
        }
    }
    public function getUserCompanyList()
    {
        $user = auth()->user();
        if($user){
            return $user->companies->where('is_registered' ,'1')->where('is_active', '1');
        }else{
            return [];
        }
    }

    public function displayStart($name)
    {
        if(isset($name) && !empty($name)) {
            return substr($name,0, 1) .'...';
        } else {
            return $name;
        }
    }

    public function userPlan()
    {
        $user = auth()->user();
        if($user) {
            return $user->plan;
        } else {
            return [];
        }
    }
    public function heicToBlob($documentFileTempPath)
    {
        $im = new \Imagick();
        $im->setFormat(config('constants.HEIC_TO_OTHER_FORMAT'));
        $im->readImage( $documentFileTempPath);
        $im->setImageCompressionQuality(60);
        return $im->getImageBlob();
    }

    function generateRandomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
	}

    public function generateEcryptedFileName($ext, $length = 20)
    {
        $fileName = $this->generateRandomString($length);
        return sha1($fileName.time()).".".$ext;
    }

    public function generatePublicId($prefix = '',$length = 15)
    {
        return strtoupper($prefix.$this->generateRandomString($length));
    }

    public function currencyByImg($currency_name)
    {
        if($currency_name == 'USD' && !empty($currency_name))
        {
            return asset('images/mipo/dollar.svg');

        } else if(in_array($currency_name, ['PYGH', 'PYG','GS', 'GS.', 'Gs', 'Gs.']) && !empty($currency_name))
        {
            return asset('images/mipo/guarani.svg');
        }
    }

    public function responsibility($name)
    { 
        $txt_name = '';
        if(isset($name) && $name == 'With') {
            $txt_name = __('With Recurso');
        } else if(isset($name) && $name == 'Without') {
            $txt_name = __('Without Recurso');
        }
        return $txt_name;
    }

    public function setDefaultImage($module_type)
    {
        if($module_type == 'operation_list') {
            return asset("images/mipo/no-image-available.svg");
        }
    }

    public function publicProfileSeller($slug = null, $page_name = 'public')
    {
        return User::when($page_name, function($qr) use ($slug, $page_name){
                if($page_name == 'public' || $page_name == 'operation_public_profile') {
                    $qr->where('slug', $slug);
                } else {
                    $qr->where('id', Auth()->user()->id);
                }
            })
            ->with([
                'favorites' => function($qry) {
                    $qry->where('user_id', Auth()->user()->id);
                },
                'ratings:id,ratingable_id,feedback_title,feedback_description,rating_number,created_by',
                'ratings.rating_by_user:id,name',
                'city:id,name',
                'issuer',
                'issuer.issuers_attach_images'
                ])->withAvg('ratings', 'rating_number')->withCount('ratings')->first();
    }

    public function publicProfileBlog($param = [], $limit = 3)
    {
        return Blog::whereHas('blog_users', function($qry) use ($param){
            $qry->where('user_id', $param['user_id']);
        })->where('is_active', '1')->orderby('id', 'desc')->limit($limit)->get();
    }

    
    public function pdfImagePath($imagePath)
    {
        if (Storage::exists($imagePath)) {
            
            // return storage_path('app/'.$imagePath);

            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = Storage::get($imagePath);
                
            return 'data:image/' . $type . ';base64,' . base64_encode($data);

        } else {
            return asset('images/blank.png');
        }
    }

    public function currencyBySymbolPDF($currency)
    {
        if($currency == 'USD')
        {
            return 'USD';
        } else if(in_array($currency, ['PYGH', 'PYG','GS', 'GS.', 'Gs', 'Gs.']) && !empty($currency))
        {
            return 'Gs.';
        } else {
            return 'USD';
        }
    }

    public function diffForHumans($data_time)
    {
        $now = Carbon::now();
        $futureDate = Carbon::parse($data_time);
        $diff = $now->diffForHumans($futureDate);
        $parts = explode(' ', $diff);

        $number = $parts[0];
        $mint_day_week_month = $parts[1];
        $after_before = $parts[2] ?? null;

        if(isset($after_before) && strtolower($after_before) == 'after') {
           return $string = __("Expired") .' '.$number .' '. __($mint_day_week_month) .' '.__('ago');
        } else {
            return $string = __("Expires in") .' '.$number .' '. __($mint_day_week_month);
        }
    }

    public function totalDays($startDate, $endDate)
    {
        if(empty($startDate)) {
            $startDate = Carbon::now();
        }
        
        if(empty($endDate)) {
            $endDate = Carbon::now();
        }
        
        $startDate = Carbon::create($startDate);
        $endDate = Carbon::create($endDate);

        $totalDays = $endDate->diffInDays($startDate);

        return $totalDays . ' '. __('days');
    }

    public function responsibilityDeal($name)
    { 
        $txt_name = '';
        if(isset($name) && $name == 'With') {
            $txt_name = 'con recurso';
        } else if(isset($name) && $name == 'Without') {
            $txt_name = 'sin recurso';
        }
        return $txt_name;
    }

    public function getRealExpireDate($expiration_date, $extra_expiration_days = 0)
    {
        if(!empty($expiration_date) && !empty($extra_expiration_days)) {
            $carbon_date = new Carbon($expiration_date);
            $real_date = Carbon::createFromDate($carbon_date)->subDays($extra_expiration_days)->format('Y-m-d');
            if(app()->getLocale() == 'es') {
                return Carbon::createFromDate($real_date)->format('j') .' de '. config('constants.MONTHS_NAME')[Carbon::createFromDate($real_date)->format('m')] .' de '. Carbon::createFromDate($real_date)->format('Y');
            } else {
                return  Carbon::createFromDate($real_date)->format('F') .' '. Carbon::createFromDate($real_date)->format('j') .', '. Carbon::createFromDate($real_date)->format('Y');
            }
        } else {
            return __('N/A');
        }
    }
}
?>
