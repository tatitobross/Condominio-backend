<?php

namespace App\Http\Controllers;

use App\Models\{Home, Resident, Visit, Payment, Incident};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CondominiumController extends Controller
{
    private function listing(Request $request, string $model, array $with = [])
    {
        $query = $model::with($with);
        if ($search = $request->string('search')->trim()->value()) $query->where(function ($q) use ($search, $model) { foreach ((new $model)->getFillable() as $field) $q->orWhere($field, 'like', "%{$search}%"); });
        foreach ($request->only(['status', 'home_id']) as $field => $value) if ($value !== null && $value !== '') $query->where($field, $value);
        return response()->json($query->latest()->paginate($request->integer('per_page', 15)));
    }
    private function crud(Request $request, ?int $id, string $model, array $rules, array $with = [])
    {
        $data = $request->validate($rules); $record = $id ? $model::findOrFail($id) : new $model;
        $record->fill($data)->save(); return response()->json(['message' => $id ? 'Registro actualizado.' : 'Registro creado.', 'data' => $record->load($with)], $id ? 200 : 201);
    }
    public function homes(Request $r) {
        if ($r->user()?->role?->role === 'Residente') {
            $homeId = Resident::where('user_id', $r->user()->id)->value('home_id');
            return response()->json(Home::whereKey($homeId)->latest()->paginate($r->integer('per_page', 15)));
        }
        return $this->listing($r, Home::class);
    }
    public function showHome(Request $r, Home $home) {
        if ($r->user()?->role?->role === 'Residente' && (int) Resident::where('user_id', $r->user()->id)->value('home_id') !== $home->id) abort(403);
        return response()->json(['data' => $home]);
    }
    public function storeHome(Request $r) { return $this->crud($r, null, Home::class, ['code'=>'required|string|max:30|unique:homes,code','block'=>'nullable|string|max:30','address'=>'required|string|max:255','status'=>'required|in:active,inactive']); }
    public function updateHome(Request $r, Home $home) { return $this->crud($r, $home->id, Home::class, ['code'=>['required','string','max:30',Rule::unique('homes','code')->ignore($home)],'block'=>'nullable|string|max:30','address'=>'required|string|max:255','status'=>'required|in:active,inactive']); }
    public function destroyHome(Home $home) { $home->delete(); return response()->noContent(); }
    public function residents(Request $r) { return $this->listing($r, Resident::class, ['home']); }
    public function showResident(Resident $resident) { return response()->json(['data' => $resident->load('home')]); }
    public function storeResident(Request $r) { return $this->crud($r, null, Resident::class, $this->residentRules(), ['home']); }
    public function updateResident(Request $r, Resident $resident) { return $this->crud($r, $resident->id, Resident::class, $this->residentRules($resident), ['home']); }
    public function destroyResident(Resident $resident) { $resident->delete(); return response()->noContent(); }
    private function residentRules(?Resident $resident = null): array { return ['home_id'=>'required|exists:homes,id','user_id'=>['nullable', Rule::unique('residents','user_id')->ignore($resident)],'name'=>'required|string|max:150','dpi'=>['nullable','string','max:30',Rule::unique('residents','dpi')->ignore($resident)],'phone'=>'nullable|string|max:30','email'=>'nullable|email|max:150','status'=>'required|in:active,inactive']; }
    public function visits(Request $r) {
        if ($r->user()?->role?->role === 'Residente') {
            $resident = Resident::where('user_id', $r->user()->id)->first();
            return response()->json(Visit::with(['home','resident'])->where('resident_id', $resident?->id ?? 0)->latest()->paginate($r->integer('per_page', 15)));
        }
        return $this->listing($r, Visit::class, ['home','resident']);
    }
    public function showVisit(Request $r, Visit $visit) {
        if ($r->user()?->role?->role === 'Residente' && $visit->resident?->user_id !== $r->user()->id) abort(403);
        return response()->json(['data' => $visit->load('home', 'resident')]);
    }
    public function storeVisit(Request $r) {
        $data = $r->validate($this->visitRules());
        if ($r->user()?->role?->role === 'Residente') {
            $resident = Resident::where('user_id', $r->user()->id)->firstOrFail();
            if ((int) $data['home_id'] !== (int) $resident->home_id) return response()->json(['message' => 'Solo puede registrar visitas para su vivienda.'], 403);
            $data['resident_id'] = $resident->id;
            $data['status'] = 'pending';
        }
        $visit = Visit::create($data);
        return response()->json(['message' => 'Visita registrada.', 'data' => $visit->load('home', 'resident')], 201);
    }
    public function updateVisit(Request $r, Visit $visit) { return $this->crud($r, $visit->id, Visit::class, $this->visitRules(), ['home','resident']); }
    public function destroyVisit(Visit $visit) { $visit->delete(); return response()->noContent(); }
    private function visitRules(): array { return ['home_id'=>'required|exists:homes,id','resident_id'=>'nullable|exists:residents,id','visitor_name'=>'required|string|max:150','visitor_dpi'=>'nullable|string|max:30','vehicle_plate'=>'nullable|string|max:20','scheduled_at'=>'required|date','status'=>'sometimes|in:pending,authorized,entered,exited,rejected']; }
    public function authorizeVisit(Visit $visit) { if (auth()->user()?->role?->role === 'Residente' && $visit->resident?->user_id !== auth()->id()) return response()->json(['message' => 'No puede autorizar visitas de otra vivienda.'], 403); if ($visit->status !== 'pending') return response()->json(['message'=>'La visita ya fue procesada.'], 422); $visit->update(['status'=>'authorized','authorized_at'=>now()]); return response()->json(['data'=>$visit]); }
    public function entry(Visit $visit) { if ($visit->status !== 'authorized') return response()->json(['message'=>'Solo una visita autorizada puede ingresar.'], 422); $visit->update(['status'=>'entered','entry_at'=>now()]); return response()->json(['data'=>$visit]); }
    public function exit(Visit $visit) { if ($visit->status !== 'entered') return response()->json(['message'=>'No hay una entrada activa para esta visita.'], 422); $visit->update(['status'=>'exited','exit_at'=>now()]); return response()->json(['data'=>$visit]); }
    public function payments(Request $r) { return $this->listing($r, Payment::class, ['home']); }
    public function showPayment(Payment $payment) { return response()->json(['data' => $payment->load('home')]); }
    public function storePayment(Request $r) { return $this->crud($r, null, Payment::class, $this->paymentRules(), ['home']); }
    public function updatePayment(Request $r, Payment $payment) { return $this->crud($r, $payment->id, Payment::class, $this->paymentRules($payment), ['home']); }
    public function destroyPayment(Payment $payment) { $payment->delete(); return response()->noContent(); }
    private function paymentRules(?Payment $payment=null): array { return ['home_id'=>'required|exists:homes,id','period'=>['required','date_format:Y-m',Rule::unique('payments')->where(fn($q)=>$q->where('home_id',request('home_id')))->ignore($payment)],'due_date'=>'required|date','amount'=>'required|numeric|min:0.01','reference'=>'nullable|string|max:100','status'=>'sometimes|in:pending,paid,overdue']; }
    public function pay(Payment $payment) {
        if ($payment->status === 'paid') return response()->json(['message'=>'La cuota ya fue pagada.'], 422);
        $payment->update(['status'=>'paid','paid_at'=>now()]);
        $payment->load('home');
        return response()->json(['message' => 'Pago registrado.', 'data' => $payment, 'invoice' => [
            'number' => 'CON-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
            'issued_at' => $payment->paid_at?->toIso8601String(),
            'home' => ['code' => $payment->home?->code, 'address' => $payment->home?->address],
            'period' => $payment->period,
            'reference' => $payment->reference,
            'amount' => $payment->amount,
        ]]);
    }
    public function incidents(Request $r) { return $this->listing($r, Incident::class, ['home','reporter']); }
    public function showIncident(Incident $incident) { return response()->json(['data' => $incident->load('home', 'reporter')]); }
    public function storeIncident(Request $r)
    {
        $data = $r->validate($this->incidentRules());
        // Fecha y usuario se registran en la API, nunca llegan como valores confiables desde Flutter.
        $data['reported_at'] = now();
        $data['reported_by'] = $r->user()->id;
        $incident = Incident::create($data);
        return response()->json(['message' => 'Incidencia registrada.', 'data' => $incident->load('home', 'reporter')], 201);
    }
    public function updateIncident(Request $r, Incident $incident) { return $this->crud($r, $incident->id, Incident::class, $this->incidentRules(), ['home','reporter']); }
    public function destroyIncident(Incident $incident) { $incident->delete(); return response()->noContent(); }
    private function incidentRules(): array { return ['home_id'=>'nullable|exists:homes,id','title'=>'required|string|max:150','description'=>'required|string','priority'=>'required|in:low,medium,high','status'=>'sometimes|in:open,in_progress,resolved']; }
    public function dashboard() { return response()->json(['homes'=>Home::where('status','active')->count(),'pending_payments'=>Payment::whereIn('status',['pending','overdue'])->count(),'open_incidents'=>Incident::whereIn('status',['open','in_progress'])->count(),'visits_today'=>Visit::whereDate('scheduled_at',today())->count()]); }
    public function export(string $module)
    {
        $models = ['homes' => Home::class, 'residents' => Resident::class, 'visits' => Visit::class, 'payments' => Payment::class, 'incidents' => Incident::class];
        abort_unless(isset($models[$module]), 404);
        $rows = $models[$module]::query()->get()->map(fn ($record) => $record->toArray());
        $columns = $rows->isEmpty() ? ['id'] : array_keys($rows->first());
        $callback = function () use ($rows, $columns): void {
            $out = fopen('php://output', 'w'); fputcsv($out, $columns);
            foreach ($rows as $row) fputcsv($out, array_map(fn ($column) => is_scalar($row[$column] ?? null) ? $row[$column] : json_encode($row[$column] ?? null), $columns));
            fclose($out);
        };
        return response()->streamDownload($callback, "{$module}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
