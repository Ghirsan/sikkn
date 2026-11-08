#!/usr/bin/env node

/**
 * PDFKit Generator for Laporan Rencana Kegiatan (LRK)
 * Exact format recreated from docs-source/lrk.docx & docs-source/lrk.pdf
 * - Section 1: Portrait (Cover, Pengesahan, Daftar Isi)
 * - Section 2: Landscape throughout all remaining chapters (Bab 1, 2, 3, 4, Lampiran 1 & 2)
 */

import fs from 'node:fs';
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
  console.error('Usage: node generate-lrk.js --input=<path> --output=<path>');
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

// Navigation / TOC tracking (stores exact 1-indexed page numbers)
const tocPageMap = {
  pengesahan: 2,
  daftarIsi: 3,
  latarBelakang: 4,
  usulanProgram: 4,
  rencanaPelaksanaan: 8,
  luaran: 9,
  lampiranSurvey: 10,
  lampiranPeta: 10
};

let tocPageIndex = 2; // 0-indexed page index for DAFTAR ISI
let tocStartY = 120;

// Page addition helpers
const addPortraitPage = () => {
  doc.addPage({
    size: 'A4',
    layout: 'portrait',
    margins: { top: 45, bottom: 45, left: 40, right: 40 }
  });
  doc.x = doc.page.margins.left;
};

const addLandscapePage = () => {
  doc.addPage({
    size: 'A4',
    layout: 'landscape',
    margins: { top: 40, bottom: 40, left: 40, right: 40 }
  });
  doc.x = doc.page.margins.left;
};

/**
 * Universal table renderer with border strokes, header, and automatic page breaks
 * Guarantees doc.x is reset to startX at completion so subsequent headings don't squish.
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

  rows.forEach((row) => {
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

  // Reset doc.x to startX and update doc.y
  doc.x = startX;
  doc.y = curY + 10;
}

// ============================================================
// SECTION 1: PORTRAIT (PAGES 1 - 3)
// ============================================================

// ------------------------------------------------------------
// PAGE 1: COVER PAGE (Portrait)
// ------------------------------------------------------------
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
  doc.y = 60;
}

// 2. Main Title
doc.fontSize(15).font('Helvetica-Bold').fillColor('#000000')
   .text('LAPORAN RENCANA KEGIATAN', { align: 'center' });

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

doc.moveDown(1.5);
doc.fontSize(11).font('Helvetica-Bold').text('Oleh :', { align: 'center' });
doc.moveDown(0.4);

// Two Column Student Roster on Cover with dynamic lineHeight to prevent overlap
const half = Math.ceil(students.length / 2);
const colLeft = students.slice(0, half);
const colRight = students.slice(half);

const rosterStartY = doc.y;
const rosterColW = (doc.page.width - 120) / 2;
const lineGap = 15;

doc.font('Helvetica').fontSize(8.5).fillColor('#000000');
colLeft.forEach((s, idx) => {
  doc.text(`${s.name} / ${s.nim}`, 60, rosterStartY + (idx * lineGap), { width: rosterColW, lineBreak: false });
});

colRight.forEach((s, idx) => {
  doc.text(`${s.name} / ${s.nim}`, 60 + rosterColW + 20, rosterStartY + (idx * lineGap), { width: rosterColW, lineBreak: false });
});

const maxLines = Math.max(colLeft.length, colRight.length);
doc.y = rosterStartY + (maxLines * lineGap) + 30;

// Institutional Footer Block (Centered across entire page width)
const coverFootY = Math.max(doc.y, doc.page.height - 145);
doc.fontSize(10).font('Helvetica-Bold').fillColor('#000000');
doc.text('PUSAT PELAYANAN KULIAH KERJA NYATA (P2KKN)', 0, coverFootY, { width: doc.page.width, align: 'center' });
doc.text('LEMBAGA PENELITIAN DAN PENGABDIAN KEPADA MASYARAKAT (LPPM)', 0, coverFootY + 16, { width: doc.page.width, align: 'center' });
doc.text('UNIVERSITAS DIPONEGORO', 0, coverFootY + 32, { width: doc.page.width, align: 'center' });
doc.text('SEMARANG', 0, coverFootY + 48, { width: doc.page.width, align: 'center' });
doc.text(`${period.year || '2025'}`, 0, coverFootY + 64, { width: doc.page.width, align: 'center' });

// ------------------------------------------------------------
// PAGE 2: HALAMAN PENGESAHAN (Portrait)
// ------------------------------------------------------------
addPortraitPage();
tocPageMap.pengesahan = doc.bufferedPageRange().count;

doc.fontSize(14).font('Helvetica-Bold').fillColor('#000000')
   .text('HALAMAN PENGESAHAN', { align: 'center' });
doc.moveDown(1.0);

const partnerPhrase = group.partner_name ? ` pada ${group.partner_name}` : '';
doc.fontSize(10).font('Helvetica').fillColor('#000000').text(
  `Tim KKN ${semesterLabel} TA ${academicYear}${partnerPhrase} di Desa ${group.village || '-'} Kecamatan ${group.district || '-'} Kota/Kabupaten ${group.regency || '-'}.`,
  { align: 'justify', lineGap: 2.5 }
);
doc.moveDown(0.8);

// Signature table with balanced row height (23 pt) so up to 19 students + signatures fit on 1 page!
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
  minRowHeight: 23,
  fontSize: 8.5,
  headerFontSize: 9
});

// Check if signature block fits on current page (requires ~110 pt)
if (doc.y + 110 > doc.page.height - doc.page.margins.bottom) {
  addPortraitPage();
}

const sigBlockY = doc.y + 10;
const sigHalfW = (doc.page.width - 80) / 2;

// Left Signer: Dosen KKN
doc.font('Helvetica').fontSize(9.5).fillColor('#000000');
doc.text('Mengetahui,', 40, sigBlockY);
doc.text('Dosen KKN,', 40, sigBlockY + 14);

const firstDpl = dpls[0] || {};
doc.fontSize(9.5).font('Helvetica-Bold').text(firstDpl.name || 'Nama Dosen KKN', 40, sigBlockY + 68);
doc.font('Helvetica').fontSize(9).text(`NIP. ${firstDpl.nip || '____________________'}`, 40, sigBlockY + 82);

// Right Signer: Kepala Desa
const rightSigX = 40 + sigHalfW;
const kadesDate = `${group.regency || 'Semarang'}, ${getPrintDate()}`;
doc.fontSize(9.5).font('Helvetica').text(kadesDate, rightSigX, sigBlockY);
doc.text('Kepala Desa,', rightSigX, sigBlockY + 14);
doc.fontSize(9.5).font('Helvetica-Bold').text(group.village_head || 'Nama dan Stempel', rightSigX, sigBlockY + 68);

// ------------------------------------------------------------
// PAGE 3: DAFTAR ISI (Portrait)
// ------------------------------------------------------------
addPortraitPage();
tocPageIndex = doc.bufferedPageRange().count - 1;
tocPageMap.daftarIsi = doc.bufferedPageRange().count;

doc.fontSize(14).font('Helvetica-Bold').fillColor('#000000').text('DAFTAR ISI', { align: 'center' });
doc.moveDown(1.5);
tocStartY = doc.y;

// Dotted Leader helper function for TOC
function renderTocLine(title, targetPage, curY) {
  const startX = 40;
  const endX = doc.page.width - 40;

  doc.fontSize(10).font('Helvetica').fillColor('#000000')
     .text(title, startX, curY, { continued: false });

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
       .text(dotStr, dotsStart, curY, { lineBreak: false });
  }

  doc.fontSize(10).font('Helvetica-Bold').fillColor('#000000')
     .text(pageNumStr, endX - pageNumW, curY);

  return curY + 22;
}

// ============================================================
// SECTION 2: LANDSCAPE THROUGHOUT ALL REMAINING CHAPTERS
// (Matching docs-source/lrk.pdf & #01 Template docx)
// ============================================================
addLandscapePage();
tocPageMap.latarBelakang = doc.bufferedPageRange().count;
tocPageMap.usulanProgram = doc.bufferedPageRange().count;

const landMarginLeft = doc.page.margins.left; // 40
const landContentW = doc.page.width - 80;     // 761.89

// ------------------------------------------------------------
// BAB 1: LATAR BELAKANG & BAB 2: USULAN RENCANA PROGRAM
// ------------------------------------------------------------
doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('1. LATAR BELAKANG', landMarginLeft, doc.y);
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').fillColor('#000000').text(
  group.background || 'Jelaskan kondisi lokasi KKN, yang meliputi: permasalahan dan potensi yang ditemukan pada saat proses observasi dan prioritas dalam pemilihan permasalahan. (500 kata)',
  landMarginLeft,
  doc.y,
  { width: landContentW, align: 'justify', lineGap: 3 }
);
doc.moveDown(1.2);

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('2. USULAN RENCANA PROGRAM', landMarginLeft, doc.y);
doc.moveDown(0.4);

// 2.1 MULTIDISIPLIN
doc.fontSize(11).font('Helvetica-Bold')
   .text('2.1 MULTIDISIPLIN', landMarginLeft, doc.y);
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  group.program_multidisiplin_text || 'Tulis usulan program multidisiplin yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya. Usulan program multidisiplin adalah setiap mahasiswa terlibat dalam 3 multidisiplin.',
  landMarginLeft,
  doc.y,
  { width: landContentW, lineGap: 2 }
);
doc.moveDown(0.6);

// Multidisiplin columns: Total width 761 pt
const mdStandardCols = [
  { key: 'no', label: 'No', width: 28, align: 'center' },
  { key: 'pelaksana', label: 'Nama / NIM / Fakultas / Prodi', width: 145, align: 'left' },
  { key: 'potensi', label: 'Potensi /Permasalahan & Lokasi / Narsum', width: 150, align: 'left' },
  { key: 'program', label: 'Usulan Program', width: 155, align: 'left' },
  { key: 'metode', label: 'Metode & Kelompok Sasaran', width: 125, align: 'left' },
  { key: 'luaran', label: 'Luaran', width: 80, align: 'left' },
  { key: 'sdg', label: 'Kategori SDG', width: 78, align: 'left' }
];

const mdVideoCols = [
  { key: 'no', label: 'No', width: 30, align: 'center' },
  { key: 'pelaksana', label: 'Nama / NIM / Fakultas / Prodi', width: 220, align: 'left' },
  { key: 'peran', label: 'Peran', width: 160, align: 'left' },
  { key: 'tanggungjawab', label: 'Deskripsi dan Tanggungjawab', width: 351, align: 'left' }
];

const allMultidisiplin = payload.multidisiplin || [];

// Group by sequence (1 = MD 1, 2 = MD 2, 3 = MD 3 / Video Profile)
const mdBySeq = {
  1: allMultidisiplin.filter(p => p.sequence === 1),
  2: allMultidisiplin.filter(p => p.sequence === 2),
  3: allMultidisiplin.filter(p => p.sequence === 3 || p.is_video_profile)
};

// Render Multidisiplin 1
const md1List = mdBySeq[1].length > 0 ? mdBySeq[1] : allMultidisiplin.slice(0, Math.ceil(allMultidisiplin.length / 2));
const md1Title = md1List[0]?.program_title || 'Peningkatan Ketahanan Pangan Masyarakat Desa';

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000')
   .text('Tabel 1. Rencana Multidisiplin 1', landMarginLeft, doc.y);
doc.moveDown(0.3);

const md1Rows = md1List.map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  potensi: p.problem_potential || '-',
  program: p.participant_title || p.program_title || '-',
  metode: `${p.method ?? ''}${p.target_audience ? '\nSasaran: ' + p.target_audience : ''}`.trim() || '-',
  luaran: p.output_target || '-',
  sdg: p.sdg_category || '-'
}));

drawTable(doc, {
  columns: mdStandardCols,
  rows: md1Rows,
  bannerTitle: `TEMA MULTIDISIPLIN 1 : "${md1Title}"`,
  fontSize: 7.5,
  headerFontSize: 8
});

// Render Multidisiplin 2
doc.moveDown(0.8);
if (doc.y + 120 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

const md2List = mdBySeq[2].length > 0 ? mdBySeq[2] : allMultidisiplin.slice(Math.ceil(allMultidisiplin.length / 2));
const md2Title = md2List[0]?.program_title || 'Pengentasan Kemiskinan Masyarakat Desa';

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000')
   .text('Tabel 2. Rencana Multidisiplin 2', landMarginLeft, doc.y);
doc.moveDown(0.3);

const md2Rows = md2List.map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  potensi: p.problem_potential || '-',
  program: p.participant_title || p.program_title || '-',
  metode: `${p.method ?? ''}${p.target_audience ? '\nSasaran: ' + p.target_audience : ''}`.trim() || '-',
  luaran: p.output_target || '-',
  sdg: p.sdg_category || '-'
}));

drawTable(doc, {
  columns: mdStandardCols,
  rows: md2Rows,
  bannerTitle: `TEMA MULTIDISIPLIN 2 : "${md2Title}"`,
  fontSize: 7.5,
  headerFontSize: 8
});

// Page break before Multidisiplin 3 (Video Profil) matching template
addLandscapePage();

// Render Multidisiplin 3 (Video Profil)
const md3List = mdBySeq[3].length > 0 ? mdBySeq[3] : [];
const md3Title = md3List[0]?.program_title || `Video Profil di Desa ${group.village || '-'} Kecamatan ${group.district || '-'}`;

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000')
   .text('Tabel 3. Rencana Multidisiplin 3', landMarginLeft, doc.y);
doc.moveDown(0.3);

const md3Rows = (md3List.length > 0 ? md3List : students.slice(0, 4)).map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name || p.name || '-'} / ${p.student_nim || p.nim || '-'} / ${p.student_fakultas || p.fakultas || '-'} / ${p.student_prodi || p.prodi || '-'}`,
  peran: p.role_in_program || 'Editor / Kameramen',
  tanggungjawab: p.responsibility || 'Mengambil footage dan menyusun video profil desa'
}));

drawTable(doc, {
  columns: mdVideoCols,
  rows: md3Rows,
  bannerTitle: `TEMA MULTIDISIPLIN 3 : "${md3Title}"`,
  fontSize: 7.5,
  headerFontSize: 8
});

// Video Storyboard & Script (Multidisiplin 3)
doc.moveDown(0.5);
doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000')
   .text('Rencana Story Board :', landMarginLeft, doc.y);
doc.moveDown(0.3);

const sbScenes = group.storyboard_scenes || [];
if (Array.isArray(sbScenes) && sbScenes.length > 0) {
  const sbCols = [
    { key: 'scene', label: 'Scene/Shot', width: 65, align: 'center' },
    { key: 'visual', label: 'Deskripsi Visual / Framing', width: 330, align: 'left' },
    { key: 'audio', label: 'Audio / Backsound', width: 286, align: 'left' },
    { key: 'duration', label: 'Durasi', width: 80, align: 'center' }
  ];
  const sbRows = sbScenes.map((s, idx) => ({
    scene: s.scene || String(idx + 1),
    visual: s.visual || '-',
    audio: s.audio || '-',
    duration: s.duration || '-'
  }));
  drawTable(doc, {
    columns: sbCols,
    rows: sbRows,
    fontSize: 7.5,
    headerFontSize: 8
  });
} else {
  doc.font('Helvetica').fontSize(8).text(
    group.storyboard_text || '- Pengambilan video sambutan Kepala Desa\n- Dokumentasi potensi alam, budaya, dan UMKM desa',
    landMarginLeft,
    doc.y,
    { width: landContentW }
  );
}

doc.moveDown(0.6);
if (doc.y + 120 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000')
   .text('Rencana Skrip Video :', landMarginLeft, doc.y);
doc.moveDown(0.3);

const vsScenes = group.video_script_scenes || [];
if (Array.isArray(vsScenes) && vsScenes.length > 0) {
  const vsCols = [
    { key: 'scene', label: 'Scene / Waktu', width: 120, align: 'center' },
    { key: 'visual', label: 'Aksi Visual On-Screen', width: 320, align: 'left' },
    { key: 'audio', label: 'Dialog / Narasi Voice-Over', width: 321, align: 'left' }
  ];
  const vsRows = vsScenes.map((s, idx) => ({
    scene: s.scene || `Scene ${idx + 1}`,
    visual: s.visual || '-',
    audio: s.audio || '-'
  }));
  drawTable(doc, {
    columns: vsCols,
    rows: vsRows,
    fontSize: 7.5,
    headerFontSize: 8
  });
} else {
  doc.font('Helvetica').fontSize(8).text(
    group.video_script_text || '- Voice over mengenalkan profil desa, fasilitas umum, dan potensi pariwisata\n- Penutup dengan ucapan terima kasih',
    landMarginLeft,
    doc.y,
    { width: landContentW }
  );
}

// Page break before 2.2 Sosial Kemasyarakatan matching template
addLandscapePage();

// ------------------------------------------------------------
// 2.2 SOSIAL KEMASYARAKATAN & 2.3 PROGRAM LAINNYA
// ------------------------------------------------------------
doc.fontSize(11).font('Helvetica-Bold').fillColor('#000000')
   .text('2.2 Sosial Kemasyarakatan', landMarginLeft, doc.y);
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  group.program_sosmas_text || 'Tulis usulan program sosial kemasyarakatan yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya. Usulan program sosial kemasyarakatan adalah setiap mahasiswa terlibat dalam 1 program sosial kemasyarakatan.',
  landMarginLeft,
  doc.y,
  { width: landContentW, lineGap: 2 }
);
doc.moveDown(0.5);

const sosmasAll = payload.sosialKemasyarakatan || [];
const saintekList = sosmasAll.filter(p => p.is_saintek);
const soshumList = sosmasAll.filter(p => !p.is_saintek);

const sosmasCols = [
  { key: 'no', label: 'No', width: 30, align: 'center' },
  { key: 'pelaksana', label: 'Nama / NIM / Fakultas / Prodi', width: 220, align: 'left' },
  { key: 'peran', label: 'Peran', width: 160, align: 'left' },
  { key: 'tanggungjawab', label: 'Deskripsi dan Tanggungjawab', width: 230, align: 'left' },
  { key: 'sdg', label: 'Kategori SDG', width: 121, align: 'left' }
];

// Tabel 4. Saintek
doc.fontSize(9.5).font('Helvetica-Bold')
   .text('Tabel 4. Rencana Sosial Kemasyarakatan Saintek (contoh)', landMarginLeft, doc.y);
doc.moveDown(0.3);

const saintekTitle = (saintekList[0] && saintekList[0].program_title) ? saintekList[0].program_title : 'Peningkatan Sanitasi & Lingkungan';
const saintekRows = (saintekList.length > 0 ? saintekList : sosmasAll.slice(0, 3)).map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  peran: p.role_in_program || 'Anggota Pelaksana',
  tanggungjawab: p.responsibility || 'Sosialisasi pengolahan limbah dan sanitasi warga',
  sdg: p.sdg_category || '-'
}));

drawTable(doc, {
  columns: sosmasCols,
  rows: saintekRows,
  bannerTitle: `Program Sosial Kemasyarakatan Kelompok (Saintek) : ${saintekTitle}`,
  fontSize: 7.5,
  headerFontSize: 8
});

// Tabel 5. Soshum
doc.moveDown(0.8);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(9.5).font('Helvetica-Bold')
   .text('Tabel 5. Rencana Sosial Kemasyarakatan Soshum (contoh)', landMarginLeft, doc.y);
doc.moveDown(0.3);

const soshumTitle = (soshumList[0] && soshumList[0].program_title) ? soshumList[0].program_title : 'Pembinaan Administrasi & Tata Kelola';
const soshumRows = (soshumList.length > 0 ? soshumList : sosmasAll.slice(3)).map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  peran: p.role_in_program || 'Anggota Pelaksana',
  tanggungjawab: p.responsibility || 'Pemberdayaan administrasi kearsipan desa dan edukasi hukum',
  sdg: p.sdg_category || '-'
}));

drawTable(doc, {
  columns: sosmasCols,
  rows: soshumRows,
  bannerTitle: `Program Sosial Kemasyarakatan Kelompok (Soshum) : ${soshumTitle}`,
  fontSize: 7.5,
  headerFontSize: 8
});

// 2.3 PROGRAM LAINNYA
doc.moveDown(0.8);
if (doc.y + 80 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(11).font('Helvetica-Bold').fillColor('#000000')
   .text('2.3 Program Lainnya', landMarginLeft, doc.y);
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  group.program_lainnya_text || 'Tulis usulan program lainnya yang disesuaikan dengan wilayah. Usulan program lainnya tidak ada batasan jumlah maupun jenis.',
  landMarginLeft,
  doc.y,
  { width: landContentW, lineGap: 2 }
);
doc.moveDown(0.5);

const lainnyaList = payload.lainnya || [];
doc.fontSize(9.5).font('Helvetica-Bold')
   .text('Tabel 6. Rencana Program Lainnya', landMarginLeft, doc.y);
doc.moveDown(0.3);

const lainnyaTitle = (lainnyaList[0] && lainnyaList[0].program_title) ? lainnyaList[0].program_title : 'Kegiatan Pendukung Lapangan';
const lainnyaRows = (lainnyaList.length > 0 ? lainnyaList : []).map((p, idx) => ({
  no: `${idx + 1}.`,
  pelaksana: `${p.student_name ?? '-'} / ${p.student_nim ?? '-'} / ${p.student_fakultas ?? '-'} / ${p.student_prodi ?? '-'}`,
  peran: p.role_in_program || 'Pendamping',
  tanggungjawab: p.responsibility || 'Membantu kegiatan kemasyarakatan dan posyandu',
  sdg: p.sdg_category || '-'
}));

if (lainnyaRows.length > 0) {
  drawTable(doc, {
    columns: sosmasCols,
    rows: lainnyaRows,
    bannerTitle: `Program Lainnya Kelompok : ${lainnyaTitle}`,
    fontSize: 7.5,
    headerFontSize: 8
  });
} else {
  doc.fontSize(8.5).font('Helvetica-Oblique').fillColor('#4b5563')
     .text('Belum ada usulan program lainnya.', landMarginLeft, doc.y);
  doc.moveDown(1);
}

// ------------------------------------------------------------
// BAB 3: RENCANA PELAKSANAAN & BAB 4: JENIS LUARAN (Landscape)
// ------------------------------------------------------------
addLandscapePage();
tocPageMap.rencanaPelaksanaan = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('3. RENCANA PELAKSANAAN', landMarginLeft, doc.y);
doc.moveDown(0.3);
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  'Tetapkan rencana pelaksanaan baik yang bersifat umum maupun rencana pelaksanaan setiap program.',
  landMarginLeft,
  doc.y,
  { width: landContentW, lineGap: 2 }
);
doc.moveDown(0.6);

// Tabel 7. Rencana Umum Kegiatan (Mingguan)
doc.fontSize(9.5).font('Helvetica-Bold')
   .text('Tabel 7. Rencana Umum Kegiatan', landMarginLeft, doc.y);
doc.moveDown(0.3);

const schedCols = [
  { key: 'no', label: 'No', width: 35, align: 'center' },
  { key: 'kegiatan', label: 'Kegiatan', width: 366, align: 'left' },
  { key: 'm1', label: '1', width: 60, align: 'center' },
  { key: 'm2', label: '2', width: 60, align: 'center' },
  { key: 'm3', label: '3', width: 60, align: 'center' },
  { key: 'm4', label: '4', width: 60, align: 'center' },
  { key: 'm5', label: '5', width: 60, align: 'center' },
  { key: 'm6', label: '6', width: 60, align: 'center' }
];

const rawSched = group.macro_schedules || [];
const schedRows = (Array.isArray(rawSched) && rawSched.length > 0)
  ? rawSched.map((item, idx) => ({
      no: `${idx + 1}.`,
      kegiatan: item.kegiatan || '-',
      m1: item.m1 ? 'v' : '',
      m2: item.m2 ? 'v' : '',
      m3: item.m3 ? 'v' : '',
      m4: item.m4 ? 'v' : '',
      m5: item.m5 ? 'v' : '',
      m6: item.m6 ? 'v' : ''
    }))
  : [
      { no: '1.', kegiatan: 'Survey Pendahuluan', m1: 'v', m2: '', m3: '', m4: '', m5: '', m6: '' },
      { no: '2.', kegiatan: 'Penyusunan Proposal / LRK', m1: 'v', m2: 'v', m3: '', m4: '', m5: '', m6: '' },
      { no: '3.', kegiatan: 'Pelaksanaan Program Kegiatan KKN', m1: '', m2: 'v', m3: 'v', m4: 'v', m5: 'v', m6: '' },
      { no: '4.', kegiatan: 'Pelaporan & Penyusunan LPK', m1: '', m2: '', m3: '', m4: '', m5: 'v', m6: 'v' },
      { no: '5.', kegiatan: 'Monev & Gelar Karya', m1: '', m2: '', m3: '', m4: 'v', m5: 'v', m6: '' }
    ];

drawTable(doc, {
  columns: schedCols,
  rows: schedRows,
  bannerTitle: 'Rencana Pelaksanaan Umum Kegiatan KKN Berdasarkan Minggu',
  fontSize: 8,
  headerFontSize: 8.5
});

// Tabel 8. Rencana Usulan Program (Kalender Berjalan)
doc.moveDown(0.8);
if (doc.y + 140 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(9.5).font('Helvetica-Bold')
   .text('Tabel 8. Rencana Usulan Program (sesuai kalender berjalan)', landMarginLeft, doc.y);
doc.moveDown(0.3);

const calendar = payload.calendar || {};
const calendarWeeks = Object.keys(calendar);
const dayColW = 108.7;
const dayHeaders = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'];

let calY = doc.y;

// Calendar Header
doc.rect(40, calY, 761, 18).fillColor('#d9d9d9').fill();
doc.fontSize(8).font('Helvetica-Bold').fillColor('#000000');
dayHeaders.forEach((lbl, i) => {
  doc.rect(40 + (i * dayColW), calY, dayColW, 18).strokeColor('#000000').lineWidth(0.5).stroke();
  doc.text(lbl, 40 + (i * dayColW), calY + 5, { width: dayColW, align: 'center' });
});
calY += 18;

calendarWeeks.forEach(weekKey => {
  const days = calendar[weekKey] || [];
  
  let maxItems = 1;
  days.forEach(d => {
    const totalItems = (d.events || []).length + (d.programs || []).length;
    if (totalItems > maxItems) maxItems = totalItems;
  });

  const cellH = Math.max(40, 14 + (maxItems * 8) + 4);

  if (calY + cellH > doc.page.height - doc.page.margins.bottom) {
    addLandscapePage();
    calY = doc.page.margins.top;
    doc.rect(40, calY, 761, 18).fillColor('#d9d9d9').fill();
    doc.fontSize(8).font('Helvetica-Bold').fillColor('#000000');
    dayHeaders.forEach((lbl, i) => {
      doc.rect(40 + (i * dayColW), calY, dayColW, 18).strokeColor('#000000').lineWidth(0.5).stroke();
      doc.text(lbl, 40 + (i * dayColW), calY + 5, { width: dayColW, align: 'center' });
    });
    calY += 18;
  }

  days.forEach((dayData, dayIdx) => {
    const curX = 40 + (dayIdx * dayColW);
    doc.rect(curX, calY, dayColW, cellH).strokeColor('#000000').lineWidth(0.5).stroke();

    if (!dayData.in_period) {
      doc.rect(curX, calY, dayColW, cellH).fillColor('#f3f4f6').fill();
    }

    // Day number
    doc.fontSize(8).font('Helvetica-Bold').fillColor('#000000')
       .text(String(dayData.day), curX + 4, calY + 3);

    // Bullet items
    let itemY = calY + 13;
    doc.fontSize(6).font('Helvetica').fillColor('#1f2937');
    (dayData.events || []).forEach(ev => {
      doc.text(`* ${ev.title}`, curX + 4, itemY, { width: dayColW - 8, lineBreak: false, ellipsis: true });
      itemY += 7.5;
    });

    (dayData.programs || []).forEach(pr => {
      doc.font('Helvetica-Bold').text(`* ${pr.code}`, curX + 4, itemY, { width: dayColW - 8, lineBreak: false });
      itemY += 7.5;
    });
  });

  calY += cellH;
});

// Dynamic Legend
const legendItems = payload.legend || [];
const legendLinesCount = legendItems.length > 0 ? Math.ceil(legendItems.length / 2) * 11 + 25 : 40;

if (calY + legendLinesCount > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
  calY = doc.page.margins.top;
}

doc.x = landMarginLeft;
doc.y = calY + 8;

// Keterangan / Legend
doc.fontSize(8.5).font('Helvetica-Bold').fillColor('#000000')
   .text('Keterangan / Legenda Kode Program :', landMarginLeft, doc.y);
doc.moveDown(0.2);

if (legendItems.length > 0) {
  doc.fontSize(7.5).font('Helvetica').fillColor('#1f2937');
  const half = Math.ceil(legendItems.length / 2);
  const leftItems = legendItems.slice(0, half);
  const rightItems = legendItems.slice(half);

  const startLegY = doc.y;
  let curLegY = startLegY;
  const col1X = landMarginLeft;
  const col2X = landMarginLeft + 380;

  leftItems.forEach(item => {
    doc.font('Helvetica-Bold').text(`* ${item.code}`, col1X, curLegY, { continued: true, width: 370 });
    doc.font('Helvetica').text(` : ${item.student_name} (${item.type_label} - ${item.program_title})`, { width: 370, lineBreak: false, ellipsis: true });
    curLegY += 10;
  });

  curLegY = startLegY;
  rightItems.forEach(item => {
    doc.font('Helvetica-Bold').text(`* ${item.code}`, col2X, curLegY, { continued: true, width: 370 });
    doc.font('Helvetica').text(` : ${item.student_name} (${item.type_label} - ${item.program_title})`, { width: 370, lineBreak: false, ellipsis: true });
    curLegY += 10;
  });

  doc.y = Math.max(startLegY + (leftItems.length * 10), startLegY + (rightItems.length * 10));
} else {
  doc.font('Helvetica').fontSize(8);
  doc.text('* M : Multidisiplin (contoh: M1-BS untuk Multidisiplin 1 - Budi Santoso)', landMarginLeft, doc.y);
  doc.text('* SK : Sosial Kemasyarakatan (contoh: SK1-BS untuk Sosmas 1 - Budi Santoso)', landMarginLeft, doc.y);
  doc.text('* L : Program Lainnya (contoh: L1-BS untuk Lainnya 1 - Budi Santoso)', landMarginLeft, doc.y);
}

// ------------------------------------------------------------
// BAB 4: JENIS-JENIS LUARAN (Landscape - matching docs-source/lrk.pdf)
// ------------------------------------------------------------
doc.moveDown(1.2);
if (doc.y + 140 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}
tocPageMap.luaran = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('4. JENIS-JENIS LUARAN', landMarginLeft, doc.y);
doc.moveDown(0.4);

doc.fontSize(9.5).font('Helvetica-Bold')
   .text('Tabel 9. Jenis-jenis Luaran', landMarginLeft, doc.y);
doc.moveDown(0.3);

const luaranCols = [
  { key: 'no', label: 'No', width: 35, align: 'center' },
  { key: 'jenis', label: 'Jenis Luaran', width: 550, align: 'left' },
  { key: 'sifat', label: 'Sifat', width: 176, align: 'center' }
];

const luaranRows = [
  { no: '1.', jenis: 'Update status kegiatan di Media Sosial', sifat: 'Wajib' },
  { no: '2.', jenis: 'Reportase Media Berita', sifat: 'Wajib' },
  { no: '3.', jenis: 'Poster / Leaflet / Lembar Balik / Prototype Produk / Teknologi / Modul / Kebijakan', sifat: 'Wajib salah satu' },
  { no: '4.', jenis: 'Video : rasio 16:9, resolusi min 1080p', sifat: 'Wajib (Multidisiplin 3)' }
];

drawTable(doc, {
  columns: luaranCols,
  rows: luaranRows,
  fontSize: 8.5,
  headerFontSize: 9
});

// ------------------------------------------------------------
// LAMPIRAN 1 & 2 (Landscape - matching docs-source/lrk.pdf)
// ------------------------------------------------------------
addLandscapePage();
tocPageMap.lampiranSurvey = doc.bufferedPageRange().count;
tocPageMap.lampiranPeta = doc.bufferedPageRange().count;

// LAMPIRAN 1 DOKUMENTASI SURVEY
doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('LAMPIRAN 1 DOKUMENTASI SURVEY', landMarginLeft, doc.y);
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').fillColor('#000000').text(
  group.survey_documentation_text || 'Masukkan dokumentasi survey pada lampiran ini, pilih foto yang memperlihatkan permasalahan atau potensi sesuai yang dipilih menjadi usulan program, sertakan juga caption foto yang sesuai. Urutkan berdasarkan urutan penyampaian usulan program dalam dokumen ini.',
  landMarginLeft,
  doc.y,
  { width: landContentW, align: 'justify', lineGap: 2.5 }
);

if (assets.survey_image_base64 && assets.survey_image_base64.includes('base64,')) {
  try {
    const b64Data = assets.survey_image_base64.split('base64,')[1];
    const imgBuf = Buffer.from(b64Data, 'base64');
    const imgObj = doc.openImage(imgBuf);

    if ((doc.page.height - doc.page.margins.bottom) - doc.y < 220) {
      addLandscapePage();
    }

    const maxH = Math.min(350, (doc.page.height - doc.page.margins.bottom) - doc.y - 40);
    const maxW = 660;
    const scale = Math.min(maxW / imgObj.width, maxH / imgObj.height);
    const renderW = Math.round(imgObj.width * scale);
    const renderH = Math.round(imgObj.height * scale);

    doc.moveDown(0.5);
    const imgX = landMarginLeft + (landContentW - renderW) / 2;
    doc.image(imgObj, imgX, doc.y, { width: renderW, height: renderH });
    doc.y += renderH + 6;

    doc.fontSize(8.5).font('Helvetica-Oblique').fillColor('#374151')
       .text(`Dokumentasi Survey Desa ${group.village || '-'}`, landMarginLeft, doc.y, { width: landContentW, align: 'center' });
    doc.y += 14;
  } catch (e) {
    // Graceful skip
  }
}

// LAMPIRAN 2 PETA LOKASI (Landscape page matching template)
addLandscapePage();
tocPageMap.lampiranPeta = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000')
   .text('LAMPIRAN 2 PETA LOKASI', landMarginLeft, doc.y);
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').fillColor('#000000').text(
  group.location_map_text || 'Gambarkan peta lokasi secara manual dengan keterangan/informasi terkait dengan permasalahan atau potensi yang menjadi usulan program KKN tersebut dilaksanakan baik multidisiplin maupun keilmuan.',
  landMarginLeft,
  doc.y,
  { width: landContentW, align: 'justify', lineGap: 2.5 }
);

if (assets.map_image_base64 && assets.map_image_base64.includes('base64,')) {
  try {
    const b64Data = assets.map_image_base64.split('base64,')[1];
    const imgBuf = Buffer.from(b64Data, 'base64');
    const imgObj = doc.openImage(imgBuf);

    if ((doc.page.height - doc.page.margins.bottom) - doc.y < 220) {
      addLandscapePage();
    }

    const maxH = Math.min(350, (doc.page.height - doc.page.margins.bottom) - doc.y - 40);
    const maxW = 660;
    const scale = Math.min(maxW / imgObj.width, maxH / imgObj.height);
    const renderW = Math.round(imgObj.width * scale);
    const renderH = Math.round(imgObj.height * scale);

    doc.moveDown(0.5);
    const imgX = landMarginLeft + (landContentW - renderW) / 2;
    doc.image(imgObj, imgX, doc.y, { width: renderW, height: renderH });
    doc.y += renderH + 6;

    doc.fontSize(8.5).font('Helvetica-Oblique').fillColor('#374151')
       .text(`Peta Lokasi KKN Desa ${group.village || '-'}`, landMarginLeft, doc.y, { width: landContentW, align: 'center' });
    doc.y += 14;
  } catch (e) {
    // Graceful skip
  }
}

// ============================================================
// TWO-PASS: DYNAMIC TABLE OF CONTENTS (Exact entries matching docs-source/lrk.pdf)
// ============================================================
const range = doc.bufferedPageRange();

doc.switchToPage(tocPageIndex);
let curTocY = tocStartY;

// 8 Standard Chapters from docs-source/lrk.pdf Page 3
curTocY = renderTocLine('HALAMAN PENGESAHAN', tocPageMap.pengesahan, curTocY);
curTocY = renderTocLine('DAFTAR ISI', tocPageMap.daftarIsi, curTocY);
curTocY = renderTocLine('1. LATAR BELAKANG', tocPageMap.latarBelakang, curTocY);
curTocY = renderTocLine('2. USULAN RENCANA PROGRAM', tocPageMap.usulanProgram, curTocY);
curTocY = renderTocLine('3. RENCANA PELAKSANAAN', tocPageMap.rencanaPelaksanaan, curTocY);
curTocY = renderTocLine('4. JENIS-JENIS LUARAN', tocPageMap.luaran, curTocY);
curTocY = renderTocLine('LAMPIRAN 1 DOKUMENTASI SURVEY', tocPageMap.lampiranSurvey, curTocY);
curTocY = renderTocLine('LAMPIRAN 2 PETA LOKASI', tocPageMap.lampiranPeta, curTocY);

doc.end();

writeStream.on('finish', () => {
  process.exit(0);
});

writeStream.on('error', (err) => {
  console.error('Write stream error:', err);
  process.exit(1);
});
