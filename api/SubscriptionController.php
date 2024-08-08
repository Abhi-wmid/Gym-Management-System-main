namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'Subscription_ID' => 'required|integer|unique:subscriptions,Subscription_ID',
            'Subscription_Lic_Number_Trial' => 'required|string',
            'Subscription_Lic_Number_1_Month' => 'required|string',
            'Subscription_Lic_Number_3_Months' => 'required|string',
            'Subscription_Lic_Number_6_Months' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $subscription = Subscription::create($request->all());
        return response()->json(['message' => 'New subscription added', 'data' => $subscription], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Subscription_Lic_Number_Trial' => 'required|string',
            'Subscription_Lic_Number_1_Month' => 'required|string',
            'Subscription_Lic_Number_3_Months' => 'required|string',
            'Subscription_Lic_Number_6_Months' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $subscription = Subscription::findOrFail($id);
        $subscription->update($request->all());
        return response()->json(['message' => 'Subscription updated', 'data' => $subscription]);
    }
}
