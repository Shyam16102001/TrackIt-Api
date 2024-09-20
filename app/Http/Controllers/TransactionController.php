<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)->get();
        return $this->apiResponse($transactions, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'group_id' => 'nullable|uuid',
            'category_id' => 'nullable|uuid',
            'amount' => 'required|numeric',
            'type' => 'required|string',
            'name' => 'required|string',
            'date' => 'required|date'
        ]);

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'group_id' => $data['group_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'],
            'name' => $data['name'],
            'date' => $data['date']
        ]);

        return $this->apiResponse($transaction, 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id == $request->user()->id) {
            return $this->apiResponse($transaction, 200);
        }
        return $this->apiResponse(403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {

        $data = $request->validate([
            'group_id' => 'nullable|uuid',
            'category_id' => 'nullable|uuid',
            'amount' => 'nullable|numeric',
            'type' => 'nullable|string',
            'name' => 'nullable|string',
        ]);

        if ($transaction->user_id == $request->user()->id) {
            $transaction->update($data);
            return $this->apiResponse('Transaction updated successfully', 204);
        }
        return $this->apiResponse(403);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction, Request $request)
    {
        if ($transaction->user_id == $request->user()->id) {
            $transaction->delete();
            return $this->apiResponse('Transaction deleted successfully', 204);
        }
        return $this->apiResponse(403);
    }
}
