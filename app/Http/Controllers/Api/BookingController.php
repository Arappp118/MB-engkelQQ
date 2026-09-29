<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return $this->success('Daftar booking', BookingResource::collection(
            $this->bookingService->listFor(auth()->user())
        ));
    }

    /**
     * Reuse StoreBookingRequest yang sama dengan Web — sudah memvalidasi
     * kepemilikan vehicle_id di withValidator(), tidak duplikasi logic.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create($request->validated(), auth()->id());

        return $this->success('Booking berhasil dibuat', new BookingResource($booking), 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        return $this->success('Detail booking', new BookingResource($booking->load('vehicle')));
    }

    public function cancel(CancelBookingRequest $request, Booking $booking): JsonResponse
    {
        try {
            $updated = $this->bookingService->cancel($booking, $request->validated('cancellation_reason', ''));
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Booking berhasil dibatalkan', new BookingResource($updated));
    }

    public function confirm(Booking $booking): JsonResponse
    {
        $this->authorize('confirm', $booking);

        try {
            $updated = $this->bookingService->confirm($booking);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Booking berhasil dikonfirmasi', new BookingResource($updated));
    }
}
