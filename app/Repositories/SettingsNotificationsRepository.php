<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Models\SupportingAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SettingsNotifications;

class SettingsNotificationsRepository extends Repository {

    public function getAll($param = [], $pagination = true)
    {
        return SettingsNotifications::get();
    }
}

?>
