<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = DB::table('reports','r')
            ->select([
                'r.id',
                'r.title',
                'r.description',
                'r.related_module',
                'r.status',
                'r.created_at',
                'r.proceed_description'
            ])
            ->where('r.reported_by', Auth::id())
            ->orderBy('r.created_at', 'desc')
            ->get();

        $summary_report = DB::table('reports','r')
            ->where('r.reported_by', Auth::id())
            ->selectRaw("
                COUNT(*) as total_sent,
                SUM(CASE WHEN r.status = 'PROCEED' THEN 1 ELSE 0 END) as total_proceed
            ")
            ->first();

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
            'count' => [
                'sent' => $summary_report->total_sent,
                'proceed' => $summary_report->total_proceed
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Reports/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'related_module' => 'required|string',
            'images.*.file' => 'file|image|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $report = Report::create([
                'title' => $request->title,
                'description' => $request->description,
                'related_module' => $request->related_module,
                'reported_by' => Auth::id(),
                'status' => 'PENDING'
            ]);
        
            if ($request->hasFile('images.*.file')) {
                $tanggal = now()->format('dmY');
                $imagesData = [];
            
                foreach ($request->file('images.*.file') as $index => $file) {
                    $extension = $file->getClientOriginalExtension();
                    $imageName = "report_{$tanggal}_image{$index}." . $extension;
            
                    $path = $file->storeAs('reports', $imageName,'public');
            
                    $imagesData[] = [
                        'report_id' => $report->id,
                        'image' => $path,
                        'created_at' => now(), // jika pakai timestamps
                        'updated_at' => now(),
                    ];
                }
            
                // Sekali insert ke DB
                ReportImages::insert($imagesData);
            }
            
        });        

        return redirect(route('reports.index'))->with('success', 'Laporan berhasil dikirim.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        $report->load('images');

        return Inertia::render('Reports/Show', [
            'report' => $report->toArray(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
