<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Messaging\Helpers\AuthParticipant;

class VoiceCallController extends Controller
{
    protected function ensureCallFeature(?string $mode = null): void
    {
        if ($mode === 'audio') {
            abort_unless(messaging_feature('audio_call'), 403, 'Audio call feature disabled.');
            return;
        }

        if ($mode === 'video') {
            abort_unless(messaging_feature('video_call'), 403, 'Video call feature disabled.');
            return;
        }

        abort_unless(
            messaging_feature('audio_call') || messaging_feature('video_call'),
            403,
            'Call feature disabled.'
        );
    }

    /**
     * Caller একটি নতুন call শুরু করে SDP offer পাঠায়
     */
    public function initiateCall(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'to_id'           => 'required|integer',
            'to_type'         => 'required|string',
            'call_mode'       => 'required|in:audio,video',
            'payload'         => 'required|string', // SDP offer JSON
        ]);
        $this->ensureCallFeature($request->string('call_mode')->value());

        $fromId   = AuthParticipant::id();
        $fromType = AuthParticipant::typeShort();

        if (!$fromId || !$fromType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // পুরনো pending offer মুছে ফেলি
        DB::table('voice_call_signals')
            ->where('conversation_id', $request->conversation_id)
            ->where('from_id', $fromId)
            ->where('from_type', $fromType)
            ->whereIn('type', ['offer', 'ringing'])
            ->delete();

        $id = DB::table('voice_call_signals')->insertGetId([
            'conversation_id' => $request->conversation_id,
            'from_id'         => $fromId,
            'from_type'       => $fromType,
            'to_id'           => $request->to_id,
            'to_type'         => $request->to_type,
            'type'            => 'offer',
            'call_mode'       => $request->call_mode,
            'payload'         => $request->payload,
            'is_processed'    => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true, 'signal_id' => $id]);
    }

    /**
     * Callee call গ্রহণ করে SDP answer পাঠায়
     */
    public function answerCall(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'to_id'           => 'required|integer',
            'to_type'         => 'required|string',
            'call_mode'       => 'required|in:audio,video',
            'payload'         => 'required|string', // SDP answer JSON
        ]);
        $this->ensureCallFeature($request->string('call_mode')->value());

        $fromId   = AuthParticipant::id();
        $fromType = AuthParticipant::typeShort();

        if (!$fromId || !$fromType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Offer টি processed হিসেবে চিহ্নিত করি
        DB::table('voice_call_signals')
            ->where('conversation_id', $request->conversation_id)
            ->where('to_id', $fromId)
            ->where('to_type', $fromType)
            ->where('type', 'offer')
            ->update(['is_processed' => true]);

        DB::table('voice_call_signals')->insert([
            'conversation_id' => $request->conversation_id,
            'from_id'         => $fromId,
            'from_type'       => $fromType,
            'to_id'           => $request->to_id,
            'to_type'         => $request->to_type,
            'type'            => 'answer',
            'call_mode'       => $request->call_mode,
            'payload'         => $request->payload,
            'is_processed'    => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Call প্রত্যাখ্যান
     */
    public function rejectCall(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'to_id'           => 'required|integer',
            'to_type'         => 'required|string',
            'call_mode'       => 'nullable|in:audio,video',
        ]);
        $this->ensureCallFeature($request->string('call_mode')->value() ?: null);

        $fromId   = AuthParticipant::id();
        $fromType = AuthParticipant::typeShort();

        if (!$fromId || !$fromType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Offer processed করি
        DB::table('voice_call_signals')
            ->where('conversation_id', $request->conversation_id)
            ->where('to_id', $fromId)
            ->where('to_type', $fromType)
            ->where('type', 'offer')
            ->update(['is_processed' => true]);

        DB::table('voice_call_signals')->insert([
            'conversation_id' => $request->conversation_id,
            'from_id'         => $fromId,
            'from_type'       => $fromType,
            'to_id'           => $request->to_id,
            'to_type'         => $request->to_type,
            'type'            => 'reject',
            'call_mode'       => $request->call_mode,
            'payload'         => null,
            'is_processed'    => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Callee রিং এ উত্তরের আগে caller কে জানান যে কল তার কাছে পৌঁছেছে
     */
    public function ringCall(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'to_id'           => 'required|integer',
            'to_type'         => 'required|string',
            'call_mode'       => 'nullable|in:audio,video',
        ]);
        $this->ensureCallFeature($request->string('call_mode')->value() ?: null);

        $fromId   = AuthParticipant::id();
        $fromType = AuthParticipant::typeShort();

        if (!$fromId || !$fromType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('voice_call_signals')->insert([
            'conversation_id' => $request->conversation_id,
            'from_id'         => $fromId,
            'from_type'       => $fromType,
            'to_id'           => $request->to_id,
            'to_type'         => $request->to_type,
            'type'            => 'ringing',
            'call_mode'       => $request->call_mode,
            'payload'         => null,
            'is_processed'    => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * ICE Candidate রিলে করা
     */
    public function sendIceCandidate(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'to_id'           => 'required|integer',
            'to_type'         => 'required|string',
            'call_mode'       => 'nullable|in:audio,video',
            'payload'         => 'required|string',
        ]);
        $this->ensureCallFeature($request->string('call_mode')->value() ?: null);

        $fromId   = AuthParticipant::id();
        $fromType = AuthParticipant::typeShort();

        if (!$fromId || !$fromType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('voice_call_signals')->insert([
            'conversation_id' => $request->conversation_id,
            'from_id'         => $fromId,
            'from_type'       => $fromType,
            'to_id'           => $request->to_id,
            'to_type'         => $request->to_type,
            'type'            => 'ice_candidate',
            'call_mode'       => $request->call_mode,
            'payload'         => $request->payload,
            'is_processed'    => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Call শেষ করা
     */
    public function hangupCall(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'to_id'           => 'required|integer',
            'to_type'         => 'required|string',
            'call_mode'       => 'nullable|in:audio,video',
        ]);
        $this->ensureCallFeature($request->string('call_mode')->value() ?: null);

        $fromId   = AuthParticipant::id();
        $fromType = AuthParticipant::typeShort();

        if (!$fromId || !$fromType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // সব পুরনো pending signal মুছে ফেলি
        DB::table('voice_call_signals')
            ->where('conversation_id', $request->conversation_id)
            ->whereIn('type', ['offer', 'ice_candidate'])
            ->where(function ($q) use ($fromId, $fromType, $request) {
                $q->where(function ($q2) use ($fromId, $fromType) {
                    $q2->where('from_id', $fromId)->where('from_type', $fromType);
                })->orWhere(function ($q2) use ($request) {
                    $q2->where('from_id', $request->to_id)->where('from_type', $request->to_type);
                });
            })
            ->delete();

        DB::table('voice_call_signals')->insert([
            'conversation_id' => $request->conversation_id,
            'from_id'         => $fromId,
            'from_type'       => $fromType,
            'to_id'           => $request->to_id,
            'to_type'         => $request->to_type,
            'type'            => 'hangup',
            'call_mode'       => $request->call_mode,
            'payload'         => null,
            'is_processed'    => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * নতুন signaling data পোল করা (incoming call, ice candidates, answer, hangup)
     */
    public function pollSignals(Request $request)
    {
        $this->ensureCallFeature();

        $myId   = AuthParticipant::id();
        $myType = AuthParticipant::typeShort();

        if (!$myId || !$myType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // আমার জন্য unprocessed signals নিই
        $signals = DB::table('voice_call_signals')
            ->where('to_id', $myId)
            ->where('to_type', $myType)
            ->where('is_processed', false)
            ->when($request->filled('conversation_id'), function ($query) use ($request) {
                $query->where('conversation_id', $request->integer('conversation_id'));
            })
            ->orderBy('id', 'asc')
            ->get();

        // সব processed করে দিই
        if ($signals->count() > 0) {
            DB::table('voice_call_signals')
                ->whereIn('id', $signals->pluck('id'))
                ->update(['is_processed' => true]);
        }

        return response()->json([
            'signals' => $signals->map(function ($s) {
                return [
                    'id'      => $s->id,
                    'conversation_id' => $s->conversation_id,
                    'type'    => $s->type,
                    'call_mode' => $s->call_mode,
                    'from_id' => $s->from_id,
                    'from_type' => $s->from_type,
                    'payload' => $s->payload,
                ];
            }),
        ]);
    }
}
