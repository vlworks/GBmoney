<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        // Получаем массив дат для текущей страницы.
        $dates = array_column($this->collection->toArray(), 'date');

        // Получаем данные из запроса.
        $incomeID = request('income_id');
        $expenseID = request('expense_id');
        $walletID = request('wallet_id');
        $type = request('type');

        // Получаем массив транзакций для каждого элемента входного массива пагинации.
        $transaction = Transaction::query()->whereIn('date', $dates)
            ->where('user_id', Auth::id())
            ->when($incomeID, function ($query) use ($incomeID) {
                return $query->where('income_id', '=', $incomeID);
            })
            ->when($expenseID, function ($query) use ($expenseID) {
                return $query->where('expense_id', '=', $expenseID);
            })
            ->when($walletID, function ($query) use ($walletID) {
                return $query->where('wallet_id_from', '=', $walletID)
                    ->orWhere('wallet_id_to', '=', $walletID);
            })
            ->when($type, function ($query) use ($type) {
                return $query->where('type', '=', $type);
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return [
            'data' => $transaction->groupBy('date')
        ];
    }
}
