namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $primaryKey = 'Subscription_ID';

    protected $fillable = [
        'Subscription_Lic_Number_Trial',
        'Subscription_Lic_Number_1_Month',
        'Subscription_Lic_Number_3_Months',
        'Subscription_Lic_Number_6_Months',
        'start_date',
        'end_date'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];
}
