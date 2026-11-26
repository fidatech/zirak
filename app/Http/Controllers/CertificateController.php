<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(Request $request): View
    {
        return view('certificates.index', [
            'certificates' => $request->user()->certificates()->with('course')->latest('issued_at')->get(),
        ]);
    }

    public function show(Request $request, Certificate $certificate): View
    {
        abort_unless($certificate->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        return view('certificates.show', ['certificate' => $certificate->load('course')]);
    }

    public function verify(Request $request, ?string $code = null): View|RedirectResponse
    {
        if (! $code && $request->filled('code')) {
            return redirect()->route('certificates.verify', strtoupper(trim($request->input('code'))));
        }

        $certificate = $code ? Certificate::with('course')->where('code', strtoupper($code))->first() : null;

        return view('certificates.verify', ['code' => $code, 'certificate' => $certificate]);
    }
}
