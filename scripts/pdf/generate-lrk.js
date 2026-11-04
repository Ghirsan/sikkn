#!/usr/bin/env node

/**
 * PDFKit Generator for Laporan Rencana Kegiatan (LRK)
 * Exact format recreated from docs-source/lrk.docx & docs-source/lrk.pdf
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

// Navigation / TOC tracking
const tocPageMap = {
  pengesahan: 2,
  daftarIsi: 3,
  latarBelakang: 4,
  usulanProgram: 4,
  rencanaPelaksanaan: 5,
  luaran: 6,
  lampiranSurvey: 6,
  lampiranPeta: 6
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

// 1. Undip Logo
if (assets.logo_path && fs.existsSync(assets.logo_path)) {
  const logoWidth = 85;
  const logoX = (doc.page.width - logoWidth) / 2;
  doc.image(assets.logo_path, logoX, 35, { width: logoWidth });
  doc.y = 135;
} else {
  doc.y = 55;
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

// Institutional Footer Block
doc.fontSize(10).font('Helvetica-Bold').fillColor('#000000')
   .text('PUSAT PELAYANAN KULIAH KERJA NYATA (P2KKN)', { align: 'center' })
   .text('LEMBAGA PENELITIAN DAN PENGABDIAN KEPADA MASYARAKAT (LPPM)', { align: 'center' })
   .text('UNIVERSITAS DIPONEGORO', { align: 'center' })
   .text('SEMARANG', { align: 'center' })
   .text(`${period.year || '2025'}`, { align: 'center' });

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
  `Tim KKN ${semesterLabel} TA ${academicYear}${partnerPhrase} di Desa ${group.village || '-'} Kecamatan ${group.district || '-'} Kota/Kabupaten ${group.regency || '-'}.`,
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
  minRowHeight: 28, // Height gives room for signature
  fontSize: 9,
  headerFontSize: 9.5
});

doc.moveDown(1.2);

// Check if signature blocks fit on same page
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
const kadesDate = `Semarang, ________________ ${period.year ? period.year + 1 : '2026'}`;
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

// Dotted Leader helper function for TOC
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
renderTocLine('2. USULAN RENCANA PROGRAM', 4);
renderTocLine('2.1 Multidisiplin', 4, true);
renderTocLine('2.2 Sosial Kemasyarakatan', 4, true);
renderTocLine('2.3 Program Lainnya', 4, true);
renderTocLine('3. RENCANA PELAKSANAAN', 5);
renderTocLine('4. JENIS-JENIS LUARAN', 6);
renderTocLine('LAMPIRAN 1 DOKUMENTASI SURVEY', 6);
renderTocLine('LAMPIRAN 2 PETA LOKASI', 6);

// ============================================================
// PAGE 4+: BAB 1 LATAR BELAKANG & BAB 2 USULAN PROGRAM (SWITCH TO LANDSCAPE)
// ============================================================
addLandscapePage();
tocPageMap.latarBelakang = doc.bufferedPageRange().count;

// 1. LATAR BELAKANG
doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('1. LATAR BELAKANG');
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').fillColor('#000000').text(
  group.background || 'Jelaskan kondisi lokasi KKN, yang meliputi: permasalahan dan potensi yang ditemukan pada saat proses observasi dan prioritas dalam pemilihan permasalahan. (500 kata)',
  { align: 'justify', lineGap: 3 }
);
doc.moveDown(1.2);

// 2. USULAN RENCANA PROGRAM
tocPageMap.usulanProgram = doc.bufferedPageRange().count;
doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('2. USULAN RENCANA PROGRAM');
doc.moveDown(0.4);

// 2.1 MULTIDISIPLIN
doc.fontSize(11).font('Helvetica-Bold').text('2.1 MULTIDISIPLIN');
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  group.program_multidisiplin_text || 'Tulis usulan program multidisiplin yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya. Usulan program multidisiplin adalah setiap mahasiswa terlibat dalam 3 multidisiplin.',
  { lineGap: 2 }
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

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000').text('Tabel 1. Rencana Multidisiplin 1');
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
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

const md2List = mdBySeq[2].length > 0 ? mdBySeq[2] : allMultidisiplin.slice(Math.ceil(allMultidisiplin.length / 2));
const md2Title = md2List[0]?.program_title || 'Pengentasan Kemiskinan Masyarakat Desa';

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000').text('Tabel 2. Rencana Multidisiplin 2');
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

// Render Multidisiplin 3 (Video Profil)
doc.moveDown(0.8);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

const md3List = mdBySeq[3].length > 0 ? mdBySeq[3] : [];
const md3Title = md3List[0]?.program_title || `Video Profil di Desa ${group.village || '-'} Kecamatan ${group.district || '-'}`;

doc.fontSize(9.5).font('Helvetica-Bold').fillColor('#000000').text('Tabel 3. Rencana Multidisiplin 3');
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

// Video Storyboard & Script
doc.moveDown(0.5);
doc.fontSize(9).font('Helvetica-Bold').fillColor('#000000').text('Rencana Story Board :');
doc.font('Helvetica').text(group.storyboard_text || '- Pengambilan video sambutan Kepala Desa\n- Dokumentasi potensi alam, budaya, dan UMKM desa');
doc.moveDown(0.4);
doc.font('Helvetica-Bold').text('Rencana Skrip Video :');
doc.font('Helvetica').text(group.video_script_text || '- Voice over mengenalkan profil desa, fasilitas umum, dan potensi pariwisata\n- Penutup dengan ucapan terima kasih');

// 2.2 SOSIAL KEMASYARAKATAN
doc.moveDown(1.2);
if (doc.y + 120 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(11).font('Helvetica-Bold').fillColor('#000000').text('2.2 Sosial Kemasyarakatan');
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  group.program_sosmas_text || 'Tulis usulan program sosial kemasyarakatan yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya. Usulan program sosial kemasyarakatan adalah setiap mahasiswa terlibat dalam 1 program sosial kemasyarakatan.',
  { lineGap: 2 }
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
doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 4. Rencana Sosial Kemasyarakatan Saintek (contoh)');
doc.moveDown(0.3);

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
  bannerTitle: 'Program Sosial Kemasyarakatan Kelompok (Saintek) : Peningkatan Sanitasi & Lingkungan',
  fontSize: 7.5,
  headerFontSize: 8
});

// Tabel 5. Soshum
doc.moveDown(0.8);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 5. Rencana Sosial Kemasyarakatan Soshum (contoh)');
doc.moveDown(0.3);

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
  bannerTitle: 'Program Sosial Kemasyarakatan Kelompok (Soshum) : Pembinaan Administrasi & Tata Kelola',
  fontSize: 7.5,
  headerFontSize: 8
});

// 2.3 PROGRAM LAINNYA
doc.moveDown(0.8);
if (doc.y + 100 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(11).font('Helvetica-Bold').fillColor('#000000').text('2.3 Program Lainnya');
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  group.program_lainnya_text || 'Tulis usulan program lainnya yang disesuaikan dengan wilayah. Usulan program lainnya tidak ada batasan jumlah maupun jenis.',
  { lineGap: 2 }
);
doc.moveDown(0.5);

const lainnyaList = payload.lainnya || [];
doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 6. Rencana Program Lainnya');
doc.moveDown(0.3);

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
    fontSize: 7.5,
    headerFontSize: 8
  });
} else {
  doc.fontSize(8.5).font('Helvetica-Oblique').fillColor('#4b5563').text('Belum ada usulan program lainnya.');
  doc.moveDown(1);
}

// ============================================================
// PAGE: BAB 3 RENCANA PELAKSANAAN (Landscape)
// ============================================================
addLandscapePage();
tocPageMap.rencanaPelaksanaan = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('3. RENCANA PELAKSANAAN');
doc.moveDown(0.3);
doc.fontSize(9).font('Helvetica').fillColor('#000000').text(
  'Tetapkan rencana pelaksanaan baik yang bersifat umum maupun rencana pelaksanaan setiap program.',
  { lineGap: 2 }
);
doc.moveDown(0.6);

// Tabel 7. Rencana Umum Kegiatan (Mingguan)
doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 7. Rencana Umum Kegiatan');
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

const defaultSchedActivities = [
  { no: '1.', kegiatan: 'Survey Pendahuluan', m1: 'v', m2: '', m3: '', m4: '', m5: '', m6: '' },
  { no: '2.', kegiatan: 'Penyusunan Proposal / LRK', m1: 'v', m2: 'v', m3: '', m4: '', m5: '', m6: '' },
  { no: '3.', kegiatan: 'Pelaksanaan Program Kegiatan KKN', m1: '', m2: 'v', m3: 'v', m4: 'v', m5: 'v', m6: '' },
  { no: '4.', kegiatan: 'Pelaporan & Penyusunan LPK', m1: '', m2: '', m3: '', m4: '', m5: 'v', m6: 'v' },
  { no: '5.', kegiatan: 'Monev & Gelar Karya', m1: '', m2: '', m3: '', m4: 'v', m5: 'v', m6: '' }
];

drawTable(doc, {
  columns: schedCols,
  rows: defaultSchedActivities,
  bannerTitle: 'Rencana Pelaksanaan Umum Kegiatan KKN Berdasarkan Minggu',
  fontSize: 8,
  headerFontSize: 8.5
});

// Tabel 8. Rencana Usulan Program (Kalender Berjalan)
doc.moveDown(0.8);
if (doc.y + 120 > doc.page.height - doc.page.margins.bottom) {
  addLandscapePage();
}

doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 8. Rencana Usulan Program (sesuai kalender berjalan)');
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
  const cellH = 46;

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
    let itemY = calY + 14;
    doc.fontSize(6.5).font('Helvetica').fillColor('#1f2937');
    (dayData.events || []).slice(0, 2).forEach(ev => {
      doc.text(`* ${ev.title}`, curX + 4, itemY, { width: dayColW - 8, lineBreak: false, ellipsis: true });
      itemY += 8.5;
    });

    (dayData.programs || []).slice(0, 2).forEach(pr => {
      doc.font('Helvetica-Bold').text(`* ${pr.code}`, curX + 4, itemY, { width: dayColW - 8, lineBreak: false });
      itemY += 8.5;
    });
  });

  calY += cellH;
});

doc.y = calY + 8;

// Keterangan / Legend
doc.fontSize(8.5).font('Helvetica-Bold').fillColor('#000000').text('Keterangan :');
doc.font('Helvetica').fontSize(8);
doc.text('* M1MA : Multidisiplin 1 Mahasiswa A');
doc.text('* SK1MA : Sosial Kemasyarakatan 1 Mahasiswa A');
doc.text('* L1MA : Lainnya 1 Mahasiswa A');

// ============================================================
// PAGE: BAB 4 & LAMPIRAN (SWITCH BACK TO PORTRAIT)
// ============================================================
addPortraitPage();
tocPageMap.luaran = doc.bufferedPageRange().count;

// 4. JENIS-JENIS LUARAN
doc.fontSize(14).font('Helvetica-Bold').fillColor('#000000').text('4. JENIS-JENIS LUARAN');
doc.moveDown(0.5);

doc.fontSize(9.5).font('Helvetica-Bold').text('Tabel 9. Jenis-jenis Luaran');
doc.moveDown(0.3);

const luaranCols = [
  { key: 'no', label: 'No', width: 35, align: 'center' },
  { key: 'jenis', label: 'Jenis Luaran', width: 345, align: 'left' },
  { key: 'sifat', label: 'Sifat', width: 135, align: 'center' }
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

// LAMPIRAN 1 DOKUMENTASI SURVEY
doc.moveDown(1.5);
tocPageMap.lampiranSurvey = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('LAMPIRAN 1 DOKUMENTASI SURVEY');
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').fillColor('#000000').text(
  group.survey_documentation_text || 'Masukkan dokumentasi survey pada lampiran ini, pilih foto yang memperlihatkan permasalahan atau potensi sesuai yang dipilih menjadi usulan program, sertakan juga caption foto yang sesuai. Urutkan berdasarkan urutan penyampaian usulan program dalam dokumen ini.',
  { align: 'justify', lineGap: 2.5 }
);

if (assets.survey_image_base64 && assets.survey_image_base64.includes('base64,')) {
  try {
    const b64Data = assets.survey_image_base64.split('base64,')[1];
    const imgBuf = Buffer.from(b64Data, 'base64');
    doc.moveDown(0.8);
    const imgW = 320;
    const imgX = (doc.page.width - imgW) / 2;
    doc.image(imgBuf, imgX, doc.y, { width: imgW });
    doc.moveDown(0.4);
    doc.fontSize(8.5).font('Helvetica-Oblique').text(`Dokumentasi Survey Desa ${group.village || '-'}`, { align: 'center' });
  } catch (e) {
    // Graceful skip if corrupt image
  }
}

// LAMPIRAN 2 PETA LOKASI
doc.moveDown(1.5);
tocPageMap.lampiranPeta = doc.bufferedPageRange().count;

doc.fontSize(13).font('Helvetica-Bold').fillColor('#000000').text('LAMPIRAN 2 PETA LOKASI');
doc.moveDown(0.4);
doc.fontSize(9.5).font('Helvetica').fillColor('#000000').text(
  group.location_map_text || 'Gambarkan peta lokasi secara manual dengan keterangan/informasi terkait dengan permasalahan atau potensi yang menjadi usulan program KKN tersebut dilaksanakan baik multidisiplin maupun keilmuan.',
  { align: 'justify', lineGap: 2.5 }
);

if (assets.map_image_base64 && assets.map_image_base64.includes('base64,')) {
  try {
    const b64Data = assets.map_image_base64.split('base64,')[1];
    const imgBuf = Buffer.from(b64Data, 'base64');
    doc.moveDown(0.8);
    const imgW = 320;
    const imgX = (doc.page.width - imgW) / 2;
    doc.image(imgBuf, imgX, doc.y, { width: imgW });
    doc.moveDown(0.4);
    doc.fontSize(8.5).font('Helvetica-Oblique').text(`Peta Lokasi KKN Desa ${group.village || '-'}`, { align: 'center' });
  } catch (e) {
    // Graceful skip if corrupt image
  }
}

// ============================================================
// TWO-PASS NUMBERING & DYNAMIC FOOTERS
// ============================================================
const range = doc.bufferedPageRange();
for (let i = range.start; i < range.start + range.count; i++) {
  doc.switchToPage(i);
  const { width, height } = doc.page;
  const contentW = width - 80;

  // Temporarily zero margins to prevent PDFKit from triggering auto-page-break
  const oldTop = doc.page.margins.top;
  const oldBottom = doc.page.margins.bottom;
  doc.page.margins.top = 0;
  doc.page.margins.bottom = 0;

  // Running Header (pages after cover)
  if (i > 0) {
    doc.fontSize(7.5).font('Helvetica').fillColor('#64748b')
       .text('KULIAH KERJA NYATA UNIVERSITAS DIPONEGORO - LAPORAN RENCANA KEGIATAN', 40, 22, {
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

  // Restore margins
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
