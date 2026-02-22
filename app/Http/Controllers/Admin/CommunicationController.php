<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunicationController extends Controller
{
    public function indexEmail(Request $request)
    {
        return $this->index($request, 'email');
    }

    public function indexSms(Request $request)
    {
        return $this->index($request, 'sms');
    }

    private function index(Request $request, $channel)
    {
        $templates = CommunicationTemplate::query()
            ->whereNull('restaurant_id') // System Templates
            ->where('channels', 'like', '%"' . $channel . '"%') // Ideally simpler if strictly one channel per template
            ->orWhere('channels', $channel) // Handle if stored as string or array
            // Actually, best to just filter by logic if complex, but let's assume we stick to model.
            ->get();

        // Refined Query:
        // Use whereRaw for generic JSON array check if possible or just get all system templates and filter 
        $templates = CommunicationTemplate::whereNull('restaurant_id')->orderBy('created_at', 'desc')->get();

        // Filter in PHP to handle array complexity
        $filtered = $templates->filter(function ($t) use ($channel) {
            $c = $t->channels; // cast to array by model
            if (is_string($c))
                $c = json_decode($c, true) ?? [];
            return in_array($channel, $c ?? []);
        })->values();

        return Inertia::render('Admin/Communication/Index', [
            'type' => $channel,
            'templates' => $filtered,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms', // passed from form hidden input
            'trigger_event' => 'required|string',
            'subject_en' => 'nullable|string|max:255',
            'subject_ar' => 'nullable|string|max:255',
            'content_en' => 'required|string',
            'content_ar' => 'required|string',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'timing_type' => 'required|in:immediately,before,after',
            'timing_days' => 'required_if:timing_type,before,after|nullable|integer|min:0',
            'timing_time' => 'required_if:timing_type,before,after|nullable|date_format:H:i',
        ]);

        if ($validated['type'] === 'sms') {
            $validated['subject_en'] = null;
            $validated['subject_ar'] = null;
        }

        CommunicationTemplate::create([
            'restaurant_id' => null, // System Template
            'name' => $validated['name'],
            'channels' => [$validated['type']], // Force array
            'trigger_event' => $validated['trigger_event'],
            'subject_en' => $validated['subject_en'],
            'subject_ar' => $validated['subject_ar'],
            'content_en' => $validated['content_en'],
            'content_ar' => $validated['content_ar'],
            'conditions' => $validated['conditions'] ?? [],
            'is_active' => $validated['is_active'],
            'timing_type' => $validated['timing_type'],
            'timing_days' => $validated['timing_days'] ?? 0,
            'timing_time' => $validated['timing_time'],
        ]);

        return redirect()->back()->with('success', 'System template created successfully.');
    }

    public function update(Request $request, CommunicationTemplate $template)
    {
        // Ensure it's a system template
        if ($template->restaurant_id !== null) {
            abort(403, 'Cannot edit restaurant templates here.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string',
            'subject_en' => 'nullable|string|max:255',
            'subject_ar' => 'nullable|string|max:255',
            'content_en' => 'required|string',
            'content_ar' => 'required|string',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'timing_type' => 'required|in:immediately,before,after',
            'timing_days' => 'required_if:timing_type,before,after|nullable|integer|min:0',
            'timing_time' => 'required_if:timing_type,before,after|nullable|date_format:H:i',
        ]);

        if (in_array('sms', $template->channels ?? [])) {
            $validated['subject_en'] = null;
            $validated['subject_ar'] = null;
        }

        $template->update(array_merge($validated, [
            'timing_days' => $validated['timing_days'] ?? 0,
        ]));

        return redirect()->back()->with('success', 'System template updated successfully.');
    }

    public function destroy(CommunicationTemplate $template)
    {
        if ($template->restaurant_id !== null) {
            abort(403, 'Cannot delete restaurant templates here.');
        }
        $template->delete();
        return redirect()->back()->with('success', 'System template deleted successfully.');
    }
}
