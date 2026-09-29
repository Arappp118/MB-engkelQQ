<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = $this->bookingService->listFor(auth()->user());

        return view('bookings.index', compact('bookings'));
    }

    public function create(): View
    {
        $this->authorize('create', Booking::class);

        return view('bookings.create');
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->create($request->validated(), auth()->id());

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Booking #{$booking->nomor_booking} berhasil dibuat.");
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['vehicle', 'customer', 'serviceOrder']);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(CancelBookingRequest $request, Booking $booking): RedirectResponse
    {
        try {
            $this->bookingService->cancel($booking, $request->validated('cancellation_reason', ''));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil dibatalkan.');
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        $this->authorize('confirm', $booking);

        try {
            $this->bookingService->confirm($booking);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil dikonfirmasi.');
    }
}
