<?php
namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Models\SupportingAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OperationsLogs;
use App\Models\User;
use Carbon\Carbon;

class Repository {

    // Investor as Buyer

    // Borrower as seller

    public $user_login_id;

    public $user_enterprise_id;

    public $user_account_type;

    public $user_details;
    
    public $dollar_sign = '$';

    public $pyg_sign = '₲';

    public $dollar_name = 'USD';

    public $pyg_name = 'Gs.';
    
    public function __construct() {

        $user_obj = Auth()->user();
        
        if(isset($user_obj)) {
            $this->user_details = $user_obj;
        
            $this->user_login_id = $user_obj?->id;
            
            $this->user_enterprise_id = $user_obj?->enterprise_id;
            
            $this->user_account_type = $user_obj?->account_type;
        } 

    }
}
?>
