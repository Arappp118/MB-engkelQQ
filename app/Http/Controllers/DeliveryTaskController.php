<?php

namespace App\Http\Controllers;

use App\Http\Requests\Delivery\AssignCourierRequest;
use App\Http\Requests\Delivery\CompleteDeliveryTaskRequest;
use App\Models\DeliveryTask;
use App\Models\User;
use App\Services\DeliveryTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryTaskController extends Controller
{
    public function __construct(protected DeliveryTaskService $deliveryTaskService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', DeliveryTask::class);

        $tasks = $this->deliveryTaskService->listFor(auth()->user());

        return view('delivery-tasks.index', compact('tasks'));
    }

    public function show(DeliveryTask $deliveryTask): View
    {
        $this->authorize('view', $deliveryTask);

        return view('delivery-tasks.show', compact('deliveryTask'));
    }

    public function assign(AssignCourierRequest $request, DeliveryTask $deliveryTask): RedirectResponse
    {
        $courier = User::findOrFail($request->validated('courier_id'));

        $this->deliveryTaskService->assignCourier($deliveryTask, $courier);

        return redirect()->route('delivery-tasks.show', $deliveryTask)
            ->with('success', 'Kurir berhasil ditugaskan.');
    }

    public function start(DeliveryTask $deliveryTask): RedirectResponse
    {
        $this->authorize('complete', $deliveryTask);

        try {
            $this->deliveryTaskService->start($deliveryTask);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('delivery-tasks.show', $deliveryTask)
            ->with('success', 'Tugas dimulai.');
    }

    public function complete(CompleteDeliveryTaskRequest $request, DeliveryTask $deliveryTask): RedirectResponse
    {
        try {
            $this->deliveryTaskService->complete($deliveryTask);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('delivery-tasks.show', $deliveryTask)
            ->with('success', 'Tugas selesai.');
    }
}
