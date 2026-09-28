<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LandingPageService
{
    public static function get()
    {
        $default = (object) [
            'brand_name'             => 'VxPOS',
            'tagline'                => 'Sistem Kasir & ERP Terpadu Multi-Toko',
            'logo'                   => null,
            'favicon'                => null,
            'hero_badge'             => '⚡ SOFTWARE ERP RETAIL & KASIR POINT OF SALE #1 DI INDONESIA',
            'hero_title'             => 'Kelola Banyak Toko & Cabang Jadi Lebih Cerdas, Cepat, dan Anti-Rugi.',
            'hero_subtitle'          => 'Aplikasi kasir multi-tenant masa kini dengan proteksi harga modal anti-kebocoran, integrasi gudang real-time, pencatatan kartu piutang otomatis, dan payroll komisi sales terpadu.',
            'cta_btn_primary_text'   => 'Coba Demo Sekarang',
            'cta_btn_primary_link'   => '/demo-login/admin',
            'cta_btn_secondary_text' => 'Konsultasi WhatsApp',
            'cta_btn_secondary_link' => '#konsultasi',
            'wa_number'              => '6281234567890',
            'wa_message'             => 'Halo Admin VxPOS, saya tertarik untuk menggunakan software kasir & ERP toko ini.',
            'stat_1_val'             => '10.000+',
            'stat_1_label'           => 'Transaksi Diproses',
            'stat_2_val'             => '99.9%',
            'stat_2_label'           => 'Akurasi Stok Gudang',
            'stat_3_val'             => '0%',
            'stat_3_label'           => 'Kebocoran Diskon',
            'pricing_starter'        => '99.000',
            'pricing_pro'            => '299.000',
            'pricing_enterprise'     => '799.000',
            'footer_desc'            => 'VxPOS adalah ekosistem Cloud Point of Sale dan Enterprise Resource Planning modern yang didesain untuk mendigitalkan toko grosir, ritel, minimarket, dan cabang usaha di seluruh Indonesia.',
            'footer_address'         => 'Jakarta & Surabaya, Indonesia',
            'footer_phone'           => '+62 812-3456-7890',
            'footer_email'           => 'support@vxpos.id',
            'copyright_text'         => 'VxPOS Point of Sale &bull; Hak Cipta by. Vicky Koroh',
        ];

        try {
            if (Schema::hasTable('landing_page_settings')) {
                $row = DB::table('landing_page_settings')->where('id', 1)->first();
                if ($row) {
                    $cleaned = clone $row;
                    $hasDeface = false;
                    foreach ($cleaned as $key => $val) {
                        if (is_string($val)) {
                            if (stripos($val, '<script') !== false || stripos($val, 'defacer') !== false || stripos($val, 'SecurityCrewz') !== false) {
                                $hasDeface = true;
                                $cleaned->$key = $default->$key ?? '';
                            } else {
                                $cleaned->$key = strip_tags($val);
                            }
                        }
                    }
                    if ($hasDeface) {
                        return $default;
                    }
                    return $cleaned;
                }
            }
        } catch (\Throwable $e) {}

        return $default;
    }
}
