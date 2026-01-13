<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    /**
     * Get all support tickets
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        $query = SupportTicket::query();

        // Filter based on user type
        if ($userType === 'Patient') {
            $query->where('user_id', $user->id)->where('user_type', 'patient');
        } elseif ($userType === 'Doctor') {
            $query->where('doctor_id', $user->id)->where('user_type', 'doctor');
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Create a new support ticket
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Only patients and doctors can create tickets
        if (!in_array($userType, ['Patient', 'Doctor'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        // SECURITY: Sanitize input to prevent XSS
        $data = [
            'category' => trim(strip_tags($request->category)),
            'subject' => trim(strip_tags($request->subject)),
            'description' => trim(strip_tags($request->description)),
            'priority' => $request->priority,
        ];
        if ($userType === 'Patient') {
            $data['user_id'] = $user->id;
            $data['user_type'] = 'patient';
        } else {
            $data['doctor_id'] = $user->id;
            $data['user_type'] = 'doctor';
        }

        $ticket = SupportTicket::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    /**
     * Get a specific ticket
     */
    public function show($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Check authorization
        if ($userType === 'Patient' && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $ticket->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Update ticket
     */
    public function update(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $user = Auth::user();

        // Only creator or admin can update
        $userType = $user->getMorphClass();
        if ($userType !== 'AdminUser' && 
            ($userType === 'Patient' && $ticket->user_id !== $user->id) &&
            ($userType === 'Doctor' && $ticket->doctor_id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'sometimes|in:open,assigned,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $ticket->update($request->only(['status', 'priority']));

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'data' => $ticket
        ]);
    }
}

