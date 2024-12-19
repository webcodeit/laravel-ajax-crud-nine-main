<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Models\SupportingAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Offer;
use App\Models\UserLevel;
use App\Models\DealsTracking;
use App\Models\OperationsLogs;

class DealsTrackingRepository extends Repository {
   
    public function getOperationByTracking($offer_id)
    {
        $result = DealsTracking::where('offer_id', $offer_id)->first();

        $seller_steps =  collect(json_decode($result->tracking_seller));

        $buyer_steps =  collect(json_decode($result->tracking_buyer));

        $all_tracking_steps =  collect(json_decode($result->all_tracking_steps));

        return array('seller_steps' => $seller_steps, 'buyer_steps' => $buyer_steps, 'all_tracking_steps' => $all_tracking_steps );
    }

    public function getOperationByLogs($operation_id)
    {
       return OperationsLogs::where('operation_id', $operation_id)->get();
    }

    public function getDealsTrackingLogs($offer_id)
    {
       return OperationsLogs::where('offer_id', $offer_id)->get();
    }
}
?>
