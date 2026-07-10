<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Payment;
use App\Models\OfficeBearer;
use App\Models\Organization;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMemberMail;

class DocumentController extends Controller
{
    public function generateReceipt(Payment $payment)
    {
        $payment->load(['member', 'member.membershipType']);

        $receiptNumber = config('organization.receipt_prefix') . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        $org = config('organization');

        // Convert image paths to base64 for DOMPDF
        $org['founder_photo_base64'] = $this->getImageBase64(public_path($org['founder_photo']));
        $org['logo_base64'] = $this->getImageBase64(public_path('images/org/logo.jpg'));

        $data = [
            'payment' => $payment,
            'member' => $payment->member,
            'receiptNumber' => $receiptNumber,
            'org' => $org,
        ];

        $pdf = PDF::setPaper('a4', 'portrait');
        $pdf = PDF::loadView('admin.documents.receipt', $data);

        return $pdf->download('Receipt_' . $receiptNumber . '.pdf');

    } // End Method

    /*
    public function generateWelcomeLetter(Member $member)
    {
        $member->load(['membershipType']);

        $org = config('organization');

        // Convert image paths to base64 for DOMPDF
        $org['founder_photo_base64'] = $this->getImageBase64(public_path($org['founder_photo']));
        $org['logo_base64'] = $this->getImageBase64(public_path('images/org/logo.jpg'));

        $data = [
            'member' => $member,
            'org' => $org,
        ];

        $pdf = PDF::setPaper('a4', 'portrait');
        $pdf = PDF::loadView('admin.documents.welcome-letter', $data);

        return $pdf->download('Welcome_' . $member->member_no . '.pdf');
    }

    private function getImageBase64($path)
    {
        if (file_exists($path)) {
            $imageData = file_get_contents($path);
            $mimeType = mime_content_type($path);
            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }
        return null;
    }
    */

    public function generateWelcomeLetter(Member $member)
    {
        $member->load('membershipType');
        $receiptMembers = collect();

        if (!empty($member->receipt_no)) {
            $receiptMembers = Member::with('membershipType')
                ->where('receipt_no', $member->receipt_no)
                ->orderBy('member_no')
                ->get();
        }

        // fallback: if receipt_no is blank or no related records found
        if ($receiptMembers->isEmpty()) {
            $receiptMembers = collect([$member]);
        }

        $org = Organization::first();

        $officeBearers = OfficeBearer::orderBy('display_order')->get();

        $president = OfficeBearer::where('position','President')->first();

        $presidentSignatureFile = public_path(
            'images/org/president_signature.png'
        );

        $presidentSignatureExists = file_exists($presidentSignatureFile);

        $presidentSignaturePath = $presidentSignatureExists
            ? str_replace('\\', '/', $presidentSignatureFile)
            : null;

        //$org->founder_photo_base64 = $this->getImageBase64(public_path($org->founder_photo));

        //$org->logo_base64 = $this->getImageBase64(public_path($org->logo));

        $org->founder_photo_base64 = $this->getImageBase64(
            storage_path('app/public/'.$org->founder_photo)
        );

        $org->logo_base64 = $this->getImageBase64(
            storage_path('app/public/'.$org->logo)
        );

        $html = view('admin.documents.welcome-letter', [
            'member' => $member,
            'receiptMembers' => $receiptMembers,
            'org' => $org,
            'officeBearers' => $officeBearers,
            'president' => $president,
            'presidentSignaturePath' => $presidentSignaturePath,
            'presidentSignatureExists' => $presidentSignatureExists,
            'today' => now()->format('F j, Y')
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 15,
            'margin_bottom' => 15
        ]);

        $mpdf->WriteHTML($html);

        // Save PDF temporarily
        $pdfContent = $mpdf->Output('', 'S');

        $pdfPath = storage_path('app/temp/welcome_'.$member->member_no.'.pdf');

        file_put_contents($pdfPath, $pdfContent);

        // Send email if email exists
        /*
        if($member->email){

            Mail::to($member->email)
                ->send(new WelcomeMemberMail($member, $pdfPath));

        }
        */
        // Open PDF in browser
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf');

    } // End Method

    private function getImageBase64($path)
    {
        if (file_exists($path)) {

            $imageData = file_get_contents($path);
            $mimeType = mime_content_type($path);

            return 'data:'.$mimeType.';base64,'.base64_encode($imageData);
        }

        return null;

    } // End Method


}
