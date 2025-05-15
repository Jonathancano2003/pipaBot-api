<?php

namespace App\Http\Controllers;

use App\Models\QuickReply;
use Illuminate\Http\Request;

class QuickReplyController extends Controller
{
    public function index()
    {
        return response()->json(QuickReply::all());
    }

    public function store(Request $request)
    {
        $request->validate(['text' => 'required|string']);
        $reply = QuickReply::create(['text' => $request->text]);
        return response()->json($reply, 201);
    }

    public function destroy($id)
    {
        QuickReply::destroy($id);
        return response()->json(['status' => 'deleted']);
    }
}
