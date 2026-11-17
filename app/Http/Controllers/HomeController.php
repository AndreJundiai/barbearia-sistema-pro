<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\GalleryImage;
use App\Models\Hairdresser;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('home.stats', 600, fn () => [
            'customers' => Customer::count(),
            'completed' => Appointment::where('status', 'completed')->count(),
        ]);

        return view('welcome', [
            'services' => Service::active()->ordered()->get(),
            'hairdressers' => Hairdresser::active()->orderBy('name')->get(),
            'gallery' => GalleryImage::latest()->limit(6)->get(),
            'stats' => $stats,
            'loyaltyTarget' => (int) Setting::get('loyalty_target', 10),
        ]);
    }

    public function privacy()
    {
        return view('privacy');
    }

    /** Manifest do app (PWA / Android) com o nome configurado no painel. */
    public function manifest()
    {
        $manifest = json_decode(file_get_contents(resource_path('manifest.json')), true);
        $name = Setting::get('shop_name');
        $manifest['name'] = $name.' - Agendamento';
        $manifest['short_name'] = \Illuminate\Support\Str::limit($name, 12, '');

        return response()->json($manifest, 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function sitemap()
    {
        $urls = [route('home'), route('booking.index'), route('gallery.index'), route('privacy')];

        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.e($url).'</loc></url>';
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }
}
