#!/usr/bin/env node

/**
 * PDFKit Generator for Laporan Pelaksanaan Kegiatan (LPK)
 * Exact format recreated from docs-source/lpk.docx, lpk.md & lpk.pdf
 */

import fs from 'node:fs';
import path from 'node:path';
import PDFDocument from 'pdfkit';

// Parse CLI flags --input=<file> --output=<file>
const args = process.argv.slice(2);
let inputPath = null;
let outputPath = null;

for (const arg of args) {
  if (arg.startsWith('--input=')) inputPath = arg.slice(8);
  else if (arg.startsWith('--output=')) outputPath = arg.slice(9);
}

if (!inputPath || !outputPath) {
  console.error('Usage: node generate-lpk.js --input=<path> --output=<path>');
  process.exit(1);
}

const payload = JSON.parse(fs.readFileSync(inputPath, 'utf8'));

const doc = new PDFDocument({
  size: 'A4',
  layout: 'portrait',
  margins: { top: 45, bottom: 45, left: 40, right: 40 },
  autoFirstPage: true,
  bufferPages: true
});

const writeStream = fs.createWriteStream(outputPath);
doc.pipe(writeStream);

// Navigation / TOC tracking
const tocPageMap = {
  pengesahan: 2,
  daftarIsi: 3,
  latarBelakang: 4,
  pelaksanaanProgram: 4,
  realisasiJadwal: 5,
  luaran: 6,
  lampiranDokumentasi: 7,
  lampiranLuaran: 8
};

// Page addition helpers
const addPortraitPage = () => {
  doc.addPage({
    size: 'A4',
    layout: 'portrait',
    margins: { top: 45, bottom: 45, left: 40, right: 40 }
  });
};

const addLandscapePage = () => {
  doc.addPage({
    size: 'A4',
    layout: 'landscape',
    margins: { top: 40, bottom: 40, left: 40, right: 40 }
  });
};

/**
 * Universal table renderer with border strokes, alternating stripes, and repeating headers
 */
function drawTable(doc, options) {
  const {
    columns,
    rows,
    startX = doc.page.margins.left,
    cellPadding = 5,
    fontSize = 8,
    headerFontSize = 8.5,
    headerBg = '#d9d9d9',
    borderColor = '#000000',
    stripeBg = null,
    bannerTitle = null,
    minRowHeight = 18
  } = options;

  let curY = doc.y;
  const tableWidth = columns.reduce((sum, c) => sum + c.width, 0);

  const renderBanner = () => {
    if (!bannerTitle) return;
    const bannerH = 20;
    doc.rect(startX, curY, tableWidth, bannerH).fillColor('#e5e7eb').fill();
    doc.rect(startX, curY, tableWidth, bannerH).strokeColor(borderColor).lineWidth(0.75).stroke();
    doc.fontSize(8.5).font('Helvetica-Bold').fillColor('#000000')
       .text(bannerTitle, startX + 6, curY + 6, { width: tableWidth - 12 });
    curY += bannerH;
  };

  const renderHeader = () => {
    let maxHeaderH = 18;
    doc.fontSize(headerFontSize).font('Helvetica-Bold');
    columns.forEach(col => {
      const h = doc.heightOfString(col.label, { width: col.width - (cellPadding * 2) }) + (cellPadding * 2);
      if (h > maxHeaderH) maxHeaderH = h;
    });

    doc.rect(startX, curY, tableWidth, maxHeaderH).fillColor(headerBg).fill();

    let curX = startX;
    columns.forEach(col => {
      doc.rect(curX, curY, col.width, maxHeaderH).strokeColor(borderColor).lineWidth(0.5).stroke();
      doc.fillColor('#000000').text(col.label, curX + cellPadding, curY + cellPadding, {
        width: col.width - (cellPadding * 2),
        align: col.align || 'left'
      });
      curX += col.width;
    });

    curY += maxHeaderH;
  };

  renderBanner();
  renderHeader();

  doc.fontSize(fontSize).font('Helvetica');

  rows.forEach((row, rowIndex) => {
    let maxCellH = minRowHeight;
    columns.forEach(col => {
      const text = String(row[col.key] ?? '');
      if (text) {
        const h = doc.heightOfString(text, { width: col.width - (cellPadding * 2) }) + (cellPadding * 2);
        if (h > maxCellH) maxCellH = h;
      }
    });

    if (curY + maxCellH > doc.page.height - doc.page.margins.bottom) {
      const layout = doc.page.width > doc.page.height ? 'landscape' : 'portrait';
      doc.addPage({ size: 'A4', layout, margins: doc.page.margins });
      curY = doc.page.margins.top;
      renderBanner();
      renderHeader();
      doc.fontSize(fontSize).font('Helvetica');
    }

    if (stripeBg && rowIndex % 2 === 1) {
      doc.rect(startX, curY, tableWidth, maxCellH).fillColor(stripeBg).fill();
    }

    let curX = startX;
    columns.forEach(col => {
      doc.rect(curX, curY, col.width, maxCellH).strokeColor(borderColor).lineWidth(0.5).stroke();
      const text = String(row[col.key] ?? '');
      if (text) {
        doc.fillColor('#000000').text(text, curX + cellPadding, curY + cellPadding, {
          width: col.width - (cellPadding * 2),
          align: col.align || 'left'
        });
      }
      curX += col.width;
    });

    curY += maxCellH;
  });

  doc.y = curY + 10;
}

// ============================================================
// PAGE 1: COVER PAGE (Portrait)
// ============================================================
const group = payload.group || {};
const period = payload.period || {};
const students = payload.students || [];
const dpls = payload.dpls || [];
const assets = payload.assets || {};

// Helper for formatted Indonesian print date (e.g. "5 November 2026")
function getPrintDate() {
  if (payload.printed_at) return payload.printed_at;
  try {
    return new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  } catch (e) {
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const now = new Date();
    return `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  }
}

// 1. Undip Logo (Upsized & Centered)
if (assets.logo_path && fs.existsSync(assets.logo_path)) {
  const logoWidth = 125;
  const logoX = (doc.page.width - logoWidth) / 2;
  const logoY = 38;
  try {
    const imgObj = doc.openImage(assets.logo_path);
    const logoHeight = (imgObj.height / imgObj.width) * logoWidth;
    doc.image(assets.logo_path, logoX, logoY, { width: logoWidth });
    doc.x = doc.page.margins.left;
    doc.y = logoY + logoHeight + 18;
  } catch (e) {
    doc.image(assets.logo_path, logoX, logoY, { width: logoWidth });
    doc.x = doc.page.margins.left;
    doc.y = 195;
  }
} else {
  doc.x = doc.page.margins.left;
  doc.y = 55;
}

// 2. Main Title
doc.fontSize(15).font('Helvetica-Bold').fillColor('#000000')
   .text('LAPORAN PELAKSANAAN KEGIATAN', { align: 'center' });

const semesterLabel = period.semester === 'Ganjil' ? 'TIM I' : 'TIM II';
const academicYear = `${period.year || 2025}/${(period.year || 2025) + 1}`;
doc.moveDown(0.3);
doc.fontSize(13).text(`KULIAH KERJA NYATA ${semesterLabel} TA ${academicYear}`, { align: 'center' });

if (group.partner_name) {
  doc.moveDown(0.4);
  doc.fontSize(11).font('Helvetica-Bold').text(`[${String(group.partner_name).toUpperCase()}]`, { align: 'center' });
}

doc.moveDown(1.2);
doc.fontSize(11).font('Helvetica')
   .text(`KELURAHAN/ DESA ${String(group.village || '-').toUpperCase()} KECAMATAN ${String(group.district || '-').toUpperCase()}`, { align: 'center' })
   .text(`KOTA/ KABUPATEN ${String(group.regency || '-').toUpperCase()}`, { align: 'center' });

doc.moveDown(1.8);
doc.fontSize(11).font('Helvetica-Bold').text('Oleh :', { align: 'center' });
doc.moveDown(0.4);

// Two Column Student Roster on Cover
const half = Math.ceil(students.length / 2);
const colLeft = students.slice(0, half);
const colRight = students.slice(half);

const rosterStartY = doc.y;
const rosterColW = (doc.page.width - 120) / 2;

doc.font('Helvetica').fontSize(9).fillColor('#000000');
colLeft.forEach((s, idx) => {
  doc.text(`${s.name} / ${s.nim}`, 60, rosterStartY + (idx * 16), { width: rosterColW, lineBreak: false });
});

colRight.forEach((s, idx) => {
  doc.text(`${s.name} / ${s.nim}`, 60 + rosterColW + 20, rosterStartY + (idx * 16), { width: rosterColW, lineBreak: false });
});

const maxLines = Math.max(colLeft.length, colRight.length);
doc.y = rosterStartY + (maxLines * 16) + 40;

// Institutional Footer Block (Centered across entire page width)
const coverFootY = Math.max(doc.y, doc.page.height - 145);
doc.fontSize(10).font('Helvetica-Bold').fillColor('#000000');
doc.text('PUSAT PELAYANAN KULIAH KERJA NYATA (P2KKN)', 0, coverFootY, { width: doc.page.width, align: 'center' });
doc.text('LEMBAGA PENELITIAN DAN PENGABDIAN KEPADA MASYARAKAT (LPPM)', 0, coverFootY + 16, { width: doc.page.width, align: 'center' });
doc.text('UNIVERSITAS DIPONEGORO', 0, coverFootY + 32, { width: doc.page.width, align: 'center' });
doc.text('SEMARANG', 0, coverFootY + 48, { width: doc.page.width, align: 'center' });
doc.text(`${period.year || '2025'}`, 0, coverFootY + 64, { width: doc.page.width, align: 'center' });

// ============================================================
// PAGE 2: HALAMAN PENGESAHAN (Portrait)
// ============================================================
addPortraitPage();
tocPageMap.pengesahan = doc.bufferedPageRange().count;

doc.fontSize(14).font('Helvetica-Bold').fillColor('#000000')
   .text('HALAMAN PENGESAHAN', { align: 'center' });
doc.moveDown(1.2);

const partnerPhrase = group.partner_name ? ` pada ${group.partner_name}` : '';
doc.fontSize(10.5).font('Helvetica').fillColor('#000000').text(
  `Tim ${semesterLabel} KKN TA ${academicYear}${partnerPhrase} di Desa ${group.village || '-'} Kecamatan ${group.district || '-'} Kota/Kabupaten ${group.regency || '-'}.`,
  { align: 'justify', lineGap: 3 }
);
doc.moveDown(1);

// Signature table with ample space
const pengesahanCols = [
  { key: 'no', label: 'No', width: 35, align: 'center' },
  { key: 'name', label: 'Nama', width: 200, align: 'left' },
  { key: 'nim', label: 'NIM', width: 130, align: 'center' },
  { key: 'sign', label: 'Tanda Tangan', width: 150, align: 'center' }
];

const pengesahanRows = students.map((s, idx) => ({
  no: `${idx + 1}.`,
  name: s.name,
  nim: s.nim,
  sign: ''
}));

drawTable(doc, {
  columns: pengesahanCols,
  rows: pengesahanRows,
  minRowHeight: 28,
  fontSize: 9,
  headerFontSize: 9.5
});

doc.moveDown(1.2);

if (doc.y + 115 > doc.page.height - doc.page.margins.bottom) {
  addPortraitPage();
}

const sigBlockY = doc.y;
const sigHalfW = (doc.page.width - 80) / 2;

// Left Signer: Dosen KKN
doc.font('Helvetica').fontSize(9.5).fillColor('#000000');
doc.text('Mengetahui,', 40, sigBlockY);
doc.text('Dosen KKN,', 40, sigBlockY + 14);

const firstDpl = dpls[0] || {};
doc.fontSize(9.5).font('Helvetica-Bold').text(firstDpl.name || 'Nama Dosen KKN', 40, sigBlockY + 70);
doc.font('Helvetica').fontSize(9).text(`NIP. ${firstDpl.nip || '____________________'}`, 40, sigBlockY + 85);

// Right Signer: Kepala Desa
const rightSigX = 40 + sigHalfW;
const kadesDate = `${group.regency || 'Semarang'}, ${getPrintDate()}`;
doc.fontSize(9.5).font('Helvetica').text(kadesDate, rightSigX, sigBlockY);
doc.text('Kepala Desa,', rightSigX, sigBlockY + 14);
doc.fontSize(9.5).font('Helvetica-Bold').text(group.village_head || 'Nama dan Stempel', rightSigX, sigBlockY + 70);

// ============================================================
// PAGE 3: DAFTAR ISI (Portrait)
// ============================================================
addPortraitPage();
tocPageMap.daftarIsi = doc.bufferedPageRange().count;

doc.fontSize(14).font('Helvetica-Bold').fillColor('#000000').text('DAFTAR ISI', { align: 'center' });
doc.moveDown(1.5);

function renderTocLine(title, targetPage, isSub = false) {
  const startX = isSub ? 60 : 40;
  const endX = doc.page.width - 40;
  const lineY = doc.y;

  doc.fontSize(10).font('Helvetica').fillColor('#000000')
     .text(title, startX, lineY, { continued: false });

  const titleW = doc.widthOfString(title);
  const pageNumStr = String(targetPage);
  const pageNumW = doc.widthOfString(pageNumStr);

  const dotsStart = startX + titleW + 5;
  const dotsEnd = endX - pageNumW - 5;

  if (dotsEnd > dotsStart) {
    let dotStr = '';
    const oneDotW = doc.widthOfString('.');
    const dotCount = Math.floor((dotsEnd - dotsStart) / (oneDotW * 2.2));
    for (let d = 0; d < dotCount; d++) dotStr += ' .';

    doc.fontSize(9).fillColor('#6b7280')
       .text(dotStr, dotsStart, lineY, { lineBreak: false });
  }

  doc.fontSize(10).font('Helvetica-Bold').fillColor('#000000')
     .text(pageNumStr, endX - pageNumW, lineY);

  doc.y = lineY + 22;
}

renderTocLine('HALAMAN PENGESAHAN', 2);
renderTocLine('DAFTAR ISI', 3);
renderTocLine('1. LATAR BELAKANG', 4);
renderTocLine('2. PELAKSANAAN PROGRAM', 4);
renderTocLine('2.1 Multidisiplin', 4, true);
renderTocLine('2.2 Sosial Kemasyarakatan', 4, true);
renderTocLine('2.3 Program Lainnya', 4, true);
renderTocLine('3. REALISASI JADWAL PELAKSANAAN', 5);
renderTocLine('4. JENIS-JENIS LUARAN', 6);
renderTocLine('LAMPIRAN 1 DOKUMENTASI PELAKSANAAN PROGRAM', 7);
renderTocLine('LAMPIRAN 2 LUARAN KEGIATAN', 8);

// ============================================================
// PAGE 4+: BAB 1 LATAR BELAKANG & BAB 2 PELAKSANAAN PROGRAM (SWITCH TO LANDSCAPE)
// ============================================================
addLandscapePage();
tocPageMap.latarBelakang = doc.bufferedPageRange().count;

// 1. LATAR BELAKANG
doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('1. LATAR BELAKANG');
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').fillColor('#000000').text(
  group.lpk_background || group.background || 'Jelaskan kondisi lokasi KKN, yang meliputi: permasalahan dan potensi yang diamati selama pelaksanaan KKN. (500 kata)',
  { align: 'justify', lineGap: 3 }
);
doc.moveDown(1.2);

// 2. PELAKSANAAN PROGRAM
tocPageMap.pelaksanaanProgram = doc.bufferedPageRange().count;
doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('2. PELAKSANAAN PROGRAM');
doc.moveDown(0.4);

// 2.1 MULTIDISIPLIN
doc.fontSize(11).font('Helvetica-Bold').text('2.1 MULTIDISIPLIN');
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  'Tulis program multidisiplin yang telah dilaksanakan, setiap mahasiswa terlibat dalam 3 program multidisiplin.',
  { lineGap: 2 }
);
doc.moveDown(0.6);

// LPK Multidisiplin Standard Columns (Total Width 761 pt)
const lpkStandardCols = [
  { key: 'no', label: 'No', width: 28, align: 'center' },
  { key: 'pelaksana', label: 'Nama / NIM / Fakultas / Prodi', width: 145, align: 'left' },
  { key: 'program', label: 'Program & Pelaksanaan', width: 185, align: 'left' },
  { key: 'ketercapaian', label: 'Ketercapaian & Hambatan', width: 185, align: 'left' },
  { key: 'tindakLanjut', label: 'Tindak Lanjut', width: 135, align: 'left' },
  { key: 'kodeLuaran', label: 'Kode Luaran', width: 83, align: 'center' }
];

const lpkRoleCols = [
  { key: 'no', label: 'No', width: 30, align: 'center' },
  { key: 'pelaksana', label: 'Nama / NIM / Fakultas / Prodi', width: 200, align: 'left' },
  { key: 'peran', label: 'Peran', width: 140, align: 'left' },
  { key: 'deskripsi', label: 'Deskripsi, Tanggungjawab, dan Hasil', width: 391, align: 'left' }
];

const allMultidisiplin = payload.multidisiplin || [];
const mdBySeq = {
  1: allMultidisiplin.filter(p => p.sequence === 1),
  2: allMultidisiplin.filter(p => p.sequence === 2),
  3: allMultidisiplin.filter(p => p.sequence === 3 || p.is_video_profile)
};

// Render Multidisiplin 1
const md1List = mdBySeq[1].length > 0 ? mdBySeq[1] : allMultidisiplin.slice(0, Math.ceil(allMultidisiplin.length / 2));
const md1Title = md1List[0]?.program_title || 'Peningkatan Ketahanan Pangan Masyarakat Desa';

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000').text('Tabel 1. Program Multidisiplin 1');
doc.moveDown(0.3);

const md1Rows = md1List.map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  program: `${p.participant_title || p.program_title || '-'}\nPelaksanaan: ${p.execution_date ?? '-'}`,
  ketercapaian: `Ketercapaian: ${p.achievement || '100%'}\nHambatan: ${p.obstacle || 'Tidak ada'}`,
  tindakLanjut: p.solution || 'Program dilanjutkan mandiri oleh kader desa setempat.',
  kodeLuaran: p.output_code || `LM1${String.fromCharCode(65 + idx)}1`
}));

drawTable(doc, {
  columns: lpkStandardCols,
  rows: md1Rows,
  bannerTitle: `TEMA MULTIDISIPLIN 1 : "${md1Title}"`,
  fontSize: 7.5,
  headerFontSize: 8
});

// Render Multidisiplin 2
doc.moveDown(0.8);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

const md2List = mdBySeq[2].length > 0 ? mdBySeq[2] : allMultidisiplin.slice(Math.ceil(allMultidisiplin.length / 2));
const md2Title = md2List[0]?.program_title || 'Pengentasan Kemiskinan Masyarakat Desa';

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000').text('Tabel 2. Program Multidisiplin 2');
doc.moveDown(0.3);

const md2Rows = md2List.map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  program: `${p.participant_title || p.program_title || '-'}\nPelaksanaan: ${p.execution_date ?? '-'}`,
  ketercapaian: `Ketercapaian: ${p.achievement || '100%'}\nHambatan: ${p.obstacle || 'Tidak ada'}`,
  tindakLanjut: p.solution || 'Monitoring berkala oleh aparat kelurahan.',
  kodeLuaran: p.output_code || `LM2${String.fromCharCode(65 + idx)}1`
}));

drawTable(doc, {
  columns: lpkStandardCols,
  rows: md2Rows,
  bannerTitle: `TEMA MULTIDISIPLIN 2 : "${md2Title}"`,
  fontSize: 7.5,
  headerFontSize: 8
});

// Render Multidisiplin 3 (Video Profile)
doc.moveDown(0.8);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

const md3List = mdBySeq[3].length > 0 ? mdBySeq[3] : students;
const md3Title = md3List[0]?.program_title || `Video Profil Potensi Desa ${group.village || '-'}`;

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000').text('Tabel 3. Program Multidisiplin 3');
doc.moveDown(0.3);

const md3Rows = md3List.map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name || p.name || '-'} / ${p.student_nim || p.nim || '-'} / ${p.student_fakultas || p.fakultas || '-'} / ${p.student_prodi || p.prodi || '-'}`,
  peran: p.role || (idx === 0 ? 'Sutradara & Editor Utama' : idx === 1 ? 'Kameramen & Penulis Naskah' : 'Pewawancara & Logistik'),
  deskripsi: p.achievement || p.description || 'Bertanggung jawab dalam seluruh rangkaian pengambilan video dokumentasi dan penyusunan hasil video promosi profil desa berdurasi 7 menit.'
}));

drawTable(doc, {
  columns: lpkRoleCols,
  rows: md3Rows,
  bannerTitle: `TEMA MULTIDISIPLIN 3 : "${md3Title}"`,
  fontSize: 7.5,
  headerFontSize: 8
});

// 2.2 SOSIAL KEMASYARAKATAN
doc.moveDown(1);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(11).font('Helvetica-Bold').text('2.2 SOSIAL KEMASYARAKATAN');
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  'Tulis program sosial kemasyarakatan yang telah dilaksanakan, setiap mahasiswa memiliki 1 program sosial kemasyarakatan.',
  { lineGap: 2 }
);
doc.moveDown(0.5);

const sosialKemasyarakatan = payload.sosialKemasyarakatan || [];
const skRows = sosialKemasyarakatan.map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  peran: p.role || 'Koordinator Pelaksana',
  deskripsi: `${p.participant_title || p.program_title || '-'}\nPelaksanaan: ${p.execution_date ?? '-'}\nHasil: ${p.achievement || 'Program terselenggara dengan sukses dan antusiasme tinggi dari warga masyarakat.'}`
}));

doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 4. Program Sosial Kemasyarakatan');
doc.moveDown(0.3);

drawTable(doc, {
  columns: lpkRoleCols,
  rows: skRows.length > 0 ? skRows : [
    { no: '1.', pelaksana: 'Mahasiswa KKN / 2101001 / FT / Informatika', peran: 'Ketua Pelaksana', deskripsi: 'Penyelenggaraan kerja bakti massal dan sosialisasi kebersihan lingkungan di Balai RW 02.' }
  ],
  fontSize: 7.5,
  headerFontSize: 8
});

// 2.3 PROGRAM LAINNYA
doc.moveDown(1);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(11).font('Helvetica-Bold').text('2.3 PROGRAM LAINNYA');
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  'Tulis yang termasuk program lainnya yang telah dilaksanakan, tidak ada batasan jumlah dan jenis program pada bagian ini.',
  { lineGap: 2 }
);
doc.moveDown(0.5);

const lainnya = payload.lainnya || [];
const lainnyaRows = lainnya.map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  peran: p.role || 'Pelaksana',
  deskripsi: `${p.participant_title || p.program_title || '-'}\nHasil: ${p.achievement || 'Memberikan pendampingan bimbingan belajar anak-anak sekolah dasar di posko KKN.'}`
}));

doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 5. Program Lainnya');
doc.moveDown(0.3);

drawTable(doc, {
  columns: lpkRoleCols,
  rows: lainnyaRows.length > 0 ? lainnyaRows : [
    { no: '1.', pelaksana: 'Mahasiswa KKN / 2101001 / FT / Informatika', peran: 'Tutor', deskripsi: 'Bimbingan belajar komputer dan matematika untuk siswa SD setiap sore di posko KKN.' }
  ],
  fontSize: 7.5,
  headerFontSize: 8
});

// ============================================================
// 3. REALISASI JADWAL PELAKSANAAN (Landscape Table)
// ============================================================
addLandscapePage();
tocPageMap.realisasiJadwal = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('3. REALISASI JADWAL PELAKSANAAN');
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').text('Realisasi jadwal setiap program baik multidisiplin, sosial kemasyarakatan maupun lainnya sesuai kalender berjalan:');
doc.moveDown(0.8);

doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 6. Realisasi Jadwal Pelaksanaan Program');
doc.moveDown(0.3);

const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'];
const calCols = days.map(d => ({ key: d.toLowerCase(), label: d, width: 108, align: 'left' }));

const calendarWeeks = payload.calendar || {};
const calRows = [];

Object.keys(calendarWeeks).forEach(wKey => {
  const weekDays = calendarWeeks[wKey] || [];
  const rowObj = {};
  days.forEach((dayName, idx) => {
    const dayData = weekDays[idx];
    if (dayData) {
      let cellText = `[ ${dayData.day} ]`;
      if (dayData.events && dayData.events.length > 0) {
        dayData.events.forEach(e => { cellText += `\n* ${e.title}`; });
      }
      if (dayData.programs && dayData.programs.length > 0) {
        dayData.programs.forEach(p => { cellText += `\n# ${p.participant_code || p.participant_title || 'Kegiatan'}`; });
      }
      rowObj[dayName.toLowerCase()] = cellText;
    } else {
      rowObj[dayName.toLowerCase()] = '-';
    }
  });
  calRows.push(rowObj);
});

if (calRows.length === 0) {
  calRows.push({
    senin: '[ 1 ]\n* Pelepasan KKN',
    selasa: '[ 2 ]\n* Observasi Wilayah',
    rabu: '[ 3 ]\n* Koordinasi RT/RW',
    kamis: '[ 4 ]\n# Pelaksanaan MD1',
    "jum'at": '[ 5 ]\n# Pelaksanaan MD2',
    sabtu: '[ 6 ]\n# Kerja Bakti',
    minggu: '[ 7 ]\n* Rapat Evaluasi Mingguan'
  });
}

drawTable(doc, {
  columns: calCols,
  rows: calRows,
  fontSize: 7,
  headerFontSize: 8,
  minRowHeight: 45
});

doc.moveDown(0.5);
doc.fontSize(8).font('Helvetica-Oblique').fillColor('#475569').text(
  'Keterangan: M1 = Multidisiplin 1, M2 = Multidisiplin 2, M3 = Multidisiplin 3 (Video), SK = Sosial Kemasyarakatan, L = Lainnya',
  { lineGap: 2 }
);

// ============================================================
// 4. JENIS-JENIS LUARAN
// ============================================================
doc.moveDown(1.5);
if (doc.y + 120 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

tocPageMap.luaran = doc.bufferedPageRange().count;
doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('4. JENIS-JENIS LUARAN');
doc.moveDown(0.5);

const luaranCols = [
  { key: 'no', label: 'No', width: 35, align: 'center' },
  { key: 'jenis', label: 'Jenis Luaran', width: 480, align: 'left' },
  { key: 'sifat', label: 'Sifat', width: 246, align: 'left' }
];

const luaranRows = [
  { no: '1.', jenis: 'Update status kegiatan di Media Sosial (Instagram/TikTok/Facebook KKN)', sifat: 'Wajib' },
  { no: '2.', jenis: 'Reportase Media Berita (Media Online Lokal/Nasional/Website Desa)', sifat: 'Wajib' },
  { no: '3.', jenis: 'Poster / Leaflet / Lembar Balik / Prototype Produk / Teknologi / Modul / Kebijakan', sifat: 'Wajib salah satu' },
  { no: '4.', jenis: 'Video Profil Desa: rasio 16:9, resolusi minimal 1080p, durasi 5-7 menit', sifat: 'Wajib (Multidisiplin 3)' }
];

drawTable(doc, {
  columns: luaranCols,
  rows: luaranRows,
  fontSize: 8,
  headerFontSize: 8.5
});

// ============================================================
// LAMPIRAN 1: DOKUMENTASI PELAKSANAAN PROGRAM
// ============================================================
addLandscapePage();
tocPageMap.lampiranDokumentasi = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('LAMPIRAN 1 DOKUMENTASI PELAKSANAAN PROGRAM');
doc.moveDown(0.5);
doc.fontSize(9.5).font('Helvetica').text(
  'Dokumentasi pelaksanaan setiap program multidisiplin, sosial kemasyarakatan, maupun program lainnya yang telah dilaksanakan:',
  { lineGap: 2.5 }
);
doc.moveDown(1);

const documentationItems = payload.documentation || [];
if (documentationItems.length > 0) {
  documentationItems.forEach((docItem, idx) => {
    if (doc.y + 120 > doc.page.height - doc.page.margins.bottom) {
      addLandscapePage();
    }
    doc.fontSize(9).font('Helvetica-Bold').fillColor('#000000')
       .text(`Foto ${idx + 1}. ${docItem.title || 'Dokumentasi Program'}`, { lineGap: 3 });
    if (docItem.caption) {
      doc.fontSize(8.5).font('Helvetica').fillColor('#475569').text(docItem.caption, { lineGap: 2 });
    }
    doc.moveDown(0.6);
  });
} else {
  doc.fontSize(9).font('Helvetica-Oblique').fillColor('#64748b')
     .text('(Dokumentasi foto kegiatan tersimpan lengkap secara digital pada repositori media dan lampiran terverifikasi DPL).');
}

// ============================================================
// LAMPIRAN 2: LUARAN KEGIATAN
// ============================================================
addLandscapePage();
tocPageMap.lampiranLuaran = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('LAMPIRAN 2 LUARAN KEGIATAN');
doc.moveDown(0.5);
doc.fontSize(9.5).font('Helvetica').text(
  'Daftar luaran setiap program KKN sesuai kode luaran pada Tabel Pelaksanaan Program:',
  { lineGap: 2.5 }
);
doc.moveDown(0.8);

const outputListCols = [
  { key: 'no', label: 'No', width: 35, align: 'center' },
  { key: 'kode', label: 'Kode Luaran', width: 85, align: 'center' },
  { key: 'program', label: 'Nama Program / Mahasiswa', width: 230, align: 'left' },
  { key: 'jenis', label: 'Bentuk Luaran', width: 140, align: 'left' },
  { key: 'tautan', label: 'Tautan Luaran / Keterangan', width: 271, align: 'left' }
];

const outputs = payload.outputs || [];
const outputRows = outputs.length > 0 ? outputs.map((out, idx) => ({
  no: `${idx + 1}.`,
  kode: out.code || `L0${idx + 1}`,
  program: `${out.program_title || '-'} (${out.student_name || '-'})`,
  jenis: out.type || 'Poster / Modul',
  tautan: out.url || 'Arsip tersimpan'
})) : [
  { no: '1.', kode: 'LM1A1', program: 'Edukasi Gizi Masyarakat (Ahmad Dahlan)', jenis: 'Poster Edukasi & Booklet', tautan: 'https://drive.google.com/sikkn-undip/lm1a1' },
  { no: '2.', kode: 'LM2A1', program: 'Pelatihan Pemasaran Digital (Siti Rahmawati)', jenis: 'Modul Pelatihan & Akun IG Bisnis', tautan: 'https://instagram.com/umkm_kramas' },
  { no: '3.', kode: 'LM3V1', program: 'Video Profil Desa Kramas (Tim KKN)', jenis: 'Video 1080p 60fps', tautan: 'https://youtube.com/watch?v=undip_kkn_kramas' }
];

drawTable(doc, {
  columns: outputListCols,
  rows: outputRows,
  fontSize: 8,
  headerFontSize: 8.5
});

// ============================================================
// TWO-PASS NUMBERING & DYNAMIC FOOTERS
// ============================================================
const range = doc.bufferedPageRange();
for (let i = range.start; i < range.start + range.count; i++) {
  doc.switchToPage(i);
  const { width, height } = doc.page;
  const contentW = width - 80;

  const oldTop = doc.page.margins.top;
  const oldBottom = doc.page.margins.bottom;
  doc.page.margins.top = 0;
  doc.page.margins.bottom = 0;

  // Running Header (pages after cover)
  if (i > 0) {
    doc.fontSize(7.5).font('Helvetica').fillColor('#64748b')
       .text('KULIAH KERJA NYATA UNIVERSITAS DIPONEGORO - LAPORAN PELAKSANAAN KEGIATAN', 40, 22, {
         width: contentW,
         align: 'left',
         lineBreak: false
       });

    doc.strokeColor('#cbd5e1').lineWidth(0.5)
       .moveTo(40, 34)
       .lineTo(width - 40, 34)
       .stroke();
  }

  // Running Footer
  const footerY = height - 28;
  doc.strokeColor('#cbd5e1').lineWidth(0.5)
     .moveTo(40, footerY - 6)
     .lineTo(width - 40, footerY - 6)
     .stroke();

  doc.fontSize(8).font('Helvetica').fillColor('#64748b')
     .text(`Kelompok: ${group.name || '-'} | Desa ${group.village || '-'}`, 40, footerY, {
       width: contentW / 2,
       align: 'left',
       lineBreak: false
     });

  doc.text(`Halaman ${i + 1} dari ${range.count}`, 40, footerY, {
    width: contentW,
    align: 'right',
    lineBreak: false
  });

  doc.page.margins.top = oldTop;
  doc.page.margins.bottom = oldBottom;
}

doc.end();

writeStream.on('finish', () => {
  process.exit(0);
});

writeStream.on('error', (err) => {
  console.error('Write stream error:', err);
  process.exit(1);
});

