<?php

namespace App\Enums;

enum SdgCategory: int
{
    case TanpaKemiskinan = 1;
    case TanpaKelaparan = 2;
    case KehidupanSehatDanSejahtera = 3;
    case PendidikanBerkualitas = 4;
    case KesetaraanGender = 5;
    case AirBersihDanSanitasiLayak = 6;
    case EnergiBersihDanTerjangkau = 7;
    case PekerjaanLayakDanPertumbuhanEkonomi = 8;
    case IndustriInovasiDanInfrastruktur = 9;
    case BerkurangnyaKesenjangan = 10;
    case KotaDanPermukimanBerkelanjutan = 11;
    case KonsumsiDanProduksiBertanggungjawab = 12;
    case PenangananPerubahanIklim = 13;
    case EkosistemLautan = 14;
    case EkosistemDaratan = 15;
    case PerdamaianKeadilanDanKelembagaan = 16;
    case KemitraanUntukMencapaiTujuan = 17;

    public function label(): string
    {
        return match ($this) {
            self::TanpaKemiskinan => 'Tanpa Kemiskinan',
            self::TanpaKelaparan => 'Tanpa Kelaparan',
            self::KehidupanSehatDanSejahtera => 'Kehidupan Sehat dan Sejahtera',
            self::PendidikanBerkualitas => 'Pendidikan Berkualitas',
            self::KesetaraanGender => 'Kesetaraan Gender',
            self::AirBersihDanSanitasiLayak => 'Air Bersih dan Sanitasi Layak',
            self::EnergiBersihDanTerjangkau => 'Energi Bersih dan Terjangkau',
            self::PekerjaanLayakDanPertumbuhanEkonomi => 'Pekerjaan Layak dan Pertumbuhan Ekonomi',
            self::IndustriInovasiDanInfrastruktur => 'Industri, Inovasi dan Infrastruktur',
            self::BerkurangnyaKesenjangan => 'Berkurangnya Kesenjangan',
            self::KotaDanPermukimanBerkelanjutan => 'Kota dan Permukiman yang Berkelanjutan',
            self::KonsumsiDanProduksiBertanggungjawab => 'Konsumsi dan Produksi yang Bertanggung Jawab',
            self::PenangananPerubahanIklim => 'Penanganan Perubahan Iklim',
            self::EkosistemLautan => 'Ekosistem Lautan',
            self::EkosistemDaratan => 'Ekosistem Daratan',
            self::PerdamaianKeadilanDanKelembagaan => 'Perdamaian, Keadilan dan Kelembagaan yang Tangguh',
            self::KemitraanUntukMencapaiTujuan => 'Kemitraan untuk Mencapai Tujuan',
        };
    }
}
