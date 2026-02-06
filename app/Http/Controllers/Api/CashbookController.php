<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Journal;
use App\Models\PaymentCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashbookController extends Controller
{

    public function cashBookApi(Request $request){
        $user = User::find($request->input('user_id'));

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid user_id'
            ], 400);
        }

        $notSuperAdmin = $user->name !== 'Super Admin'; 
        $staffId   = $user->id;
        $staffName = $user->name;

		$startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
		
        $firstCollectionDate = PaymentCollection::where('is_approve', 1)
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at')
            ->value('created_at');

        $firstExpenseDate = Journal::where('is_debit', 1)
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->whereHas('payment', function ($q) use ($user) {
                    $q->where('staff_id', $user->id);
                });
            })
            ->orderBy('created_at')
            ->value('created_at');
		
		$pastCollections = PaymentCollection::where('is_approve', 1)
            ->where('created_at', '<', Carbon::parse($startDate)->startOfDay())
            ->when($notSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->sum('collection_amount');

        $pastExpenses = Journal::where('is_debit', 1)
            ->where('created_at', '<', Carbon::parse($startDate)->startOfDay())
            ->when($notSuperAdmin, function ($q) use ($user) {
                $q->whereHas('payment', fn ($p) => $p->where('staff_id', $user->id));
            })
            ->sum('transaction_amount');
		
        $openingBalance = $pastCollections - $pastExpenses;
		
		// Total Collections
        $collectionQuery = $this->getCollectionQuery($user, $notSuperAdmin, $startDate, $endDate);
        $totalCollections = $collectionQuery->sum('collection_amount');
		
		//Cash Collection
		$totalcashCollections = $this->getCollectionQuery($user, $notSuperAdmin, $startDate, $endDate)
            ->where('payment_type', 'cash')
            ->sum('collection_amount');
		
		// NEFT Collection
        $totalneftCollections = $this->getCollectionQuery($user, $notSuperAdmin, $startDate, $endDate)
            ->where('payment_type', 'neft')
            ->sum('collection_amount');
		
		// Cheque Collection
        $totalchequeCollections = $this->getCollectionQuery($user, $notSuperAdmin, $startDate, $endDate)
            ->where('payment_type', 'cheque')
            ->sum('collection_amount');
		
		// Total Expenses
         $expenseQuery = Journal::where('is_debit', 1)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->whereHas('payment', function ($q) use ($user) {
                    $q->whereNotNull('staff_id')->where('staff_id', $user->id);
                });
            });
           
        
        $totalExpenses = $expenseQuery->sum('transaction_amount');
		
		$totalWallet = $openingBalance + ($totalCollections - $totalExpenses);
		
		// Get payment collections for table
        $paymentCollections = $this->getCollectionQuery($user, $notSuperAdmin, $startDate, $endDate)
            ->where('collection_amount', '>', 0)
            ->with(['stores', 'users'])
            ->orderByDesc('created_at')
            ->get();
		
		 // Get expenses for table
        $validPaymentIds = Journal::whereNotNull('payment_id')->pluck('payment_id');
        $paymentExpenses = Payment::where('payment_for', 'debit')
            ->whereIn('id', $validPaymentIds)
            ->whereBetween('created_at', [
				Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
			])
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->where('staff_id', $user->id);
            })
            ->with(['staff', 'store', 'supplier', 'creator', 'partner','updater'])
            ->orderByDesc('created_at')
            ->get();
		
		// ------------------------------------
		// Total Sales (Final Amount)
		// ------------------------------------
        $orderSalesQuery = Order::query()
			->whereBetween('created_at', [
				Carbon::parse($startDate)->startOfDay(),
				Carbon::parse($endDate)->endOfDay()
			])
			->whereNotNull('final_amount');

		$orderSalesQuery->when($notSuperAdmin, function ($q) use ($user) {
			$q->where('user_id', $user->id);
		});
		
		$totalOrderSales = $orderSalesQuery->sum('final_amount');
		
		 return response()->json([
            'status' => true,
            'staff' => [
                'id' => $staffId,
                'name' => $staffName
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'firstcoll' => $firstCollectionDate,
                'firstExpensedate' => $firstExpenseDate,
                'past_coll' => $pastCollections,
                'pastExpense' => $pastExpenses,
                'openingBalance' => $openingBalance,
                'total_sales' => $totalOrderSales,
                'total_collections' => $totalCollections,
                'cash_collections' => $totalcashCollections,
                'neft_collections' => $totalneftCollections,
                'cheque_collections' => $totalchequeCollections,
                'total_expenses' => $totalExpenses,
                'total_wallet' => $totalWallet,
            ],
        ]);
	}


		private function getCollectionQuery(
		$user,
        $notSuperAdmin,
		$startDate,
		$endDate
	) {
		return PaymentCollection::where('is_approve', 1)
            ->when($notSuperAdmin, function ($query) use ($user) {
				$query->where('user_id', $user->id);
			})
			->whereBetween('created_at', [
				Carbon::parse($startDate)->startOfDay(),
				Carbon::parse($endDate)->endOfDay()
			]);
	}

}
