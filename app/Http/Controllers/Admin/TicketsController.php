<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ticket\BulkDestroyTicket;
use App\Http\Requests\Admin\Ticket\DestroyTicket;
use App\Http\Requests\Admin\Ticket\IndexTicket;
use App\Http\Requests\Admin\Ticket\StoreTicket;
use App\Http\Requests\Admin\Ticket\UpdateTicket;
use App\Models\Ticket;
use Brackets\AdminListing\Facades\AdminListing;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexTicket $request
     * @return array|Factory|View
     */
    public function index(IndexTicket $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Ticket::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'passenger_id', 'schedule_id', 'deleted'],

            // set columns to searchIn
            ['id']
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.ticket.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('admin.ticket.create');

        return view('admin.ticket.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTicket $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StoreTicket $request)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Store the Ticket
        $ticket = Ticket::create($sanitized);

        if ($request->ajax()) {
            return ['redirect' => url('admin/tickets'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/tickets');
    }

    /**
     * Display the specified resource.
     *
     * @param Ticket $ticket
     * @throws AuthorizationException
     * @return void
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('admin.ticket.show', $ticket);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Ticket $ticket
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Ticket $ticket)
    {
        $this->authorize('admin.ticket.edit', $ticket);


        return view('admin.ticket.edit', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTicket $request
     * @param Ticket $ticket
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdateTicket $request, Ticket $ticket)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Update changed values Ticket
        $ticket->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/tickets'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/tickets');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyTicket $request
     * @param Ticket $ticket
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyTicket $request, Ticket $ticket)
    {
        $ticket->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyTicket $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyTicket $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Ticket::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }
}
