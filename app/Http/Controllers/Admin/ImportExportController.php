<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MembersImport;
use App\Exports\MembersTemplateExport;
use App\Exports\MembersExport;

class ImportExportController extends Controller
{
    public function index()
    {
        return view('admin.import-export.index');
    }

    public function downloadTemplate()
    {
        return Excel::download(new MembersTemplateExport(), 'members_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new MembersImport();
            Excel::import($import, $request->file('file'));

            $results = $import->getResults();

            return redirect()->route('admin.import-export.index')
                ->with('success', "Import completed. Inserted: {$results['inserted']}, Updated: {$results['updated']}, Failed: {$results['failed']}");
        } catch (\Exception $e) {
            return redirect()->route('admin.import-export.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $status = $request->get('status');
        $membershipType = $request->get('membership_type_id');

        $query = Member::with('membershipType');

        if ($status) {
            $query->where('status', $status);
        }

        if ($membershipType) {
            $query->where('membership_type_id', $membershipType);
        }

        $members = $query->orderBy('last_name')->orderBy('first_name')->get();

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.members-pdf', compact('members'));
            return $pdf->download('members_' . date('Y-m-d') . '.pdf');
        }

        return Excel::download(new MembersExport($members), 'members_' . date('Y-m-d') . '.' . $format);
    }
}
