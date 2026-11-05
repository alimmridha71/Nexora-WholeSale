<?php

namespace App\Http\Controllers;

use App\Actions\ExpireStaleQuotes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Scheduled-job endpoint for serverless hosts (Vercel Cron), which can only
 * trigger jobs over HTTP. On servers and Docker the Laravel scheduler runs
 * `nexora:expire-quotes` instead.
 *
 * - 404 unless CRON_SECRET is configured (disabled by default).
 * - Requires `Authorization: Bearer <CRON_SECRET>` (Vercel sends this
 *   header automatically), compared in constant time.
 * - Rate limited; the job itself is idempotent.
 */
class CronController extends Controller
{
    public function expireQuotes(Request $request, ExpireStaleQuotes $expire): JsonResponse
    {
        $secret = (string) config('nexora.cron_secret');

        abort_if($secret === '', 404);
        abort_unless(hash_equals('Bearer '.$secret, (string) $request->header('Authorization')), 401);

        return response()->json(['expired' => $expire()]);
    }
}
