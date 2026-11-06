#!/usr/bin/env node

/**
 * PDFKit Generator for Buku Catatan Harian (Logbook)
 * Exact format recreated from docs-source/logbook.pdf, logbook.md & #03 - Template_BUKU CATATAN HARIAN.docx
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
  console.error('Usage: node generate-logbook.js --input=<path> --output=<path>');
  process.exit(1);
}

const payload = JSON.parse(fs.readFileSync(inputPath, 'utf8'));

// Exact A4 dimensions: 595 x 842 pt
const doc = new PDFDocument({
  size: 'A4',
  layout: 'portrait',
  margins: { top: 60, bottom: 50, left: 72, right: 72 },
  autoFirstPage: true,
  bufferPages: true
});

const writeStream = fs.createWriteStream(outputPath);
doc.pipe(writeStream);

const student = payload.student || {};
const group = payload.group || {};
const period = payload.period || {};
const dpl = payload.dpl || {};
const logsGroupedByWeek = payload.logsGroupedByWeek || {};
const assets = payload.assets || {};

const tableX = 72;
const tableWidth = 451; // 595 - 144 = 451 pt (1 inch margins on both sides)

// Resolve Undip Logo
const resolveLogo = () => {
  if (assets.logo_path && fs.existsSync(assets.logo_path)) return assets.logo_path;
  const pLogbook = path.resolve('public/images/logo-undip-logbook.png');
  if (fs.existsSync(pLogbook)) return pLogbook;
  const pStandard = path.resolve('public/images/logo-undip.png');
  if (fs.existsSync(pStandard)) return pStandard;
  return null;
};
const logoPath = resolveLogo();

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

const weekKeys = Object.keys(logsGroupedByWeek);
const activeWeeks = weekKeys.length > 0 ? weekKeys : ['1'];

let isFirstDocumentPage = true;

activeWeeks.forEach((weekNum) => {
  const weekLogs = logsGroupedByWeek[weekNum] || [];

  // =========================================================================
  // PAGE 1: PENGESAHAN BUKU CATATAN HARIAN (Matches docs-source/logbook.pdf p.1)
  // =========================================================================
  if (!isFirstDocumentPage) {
    doc.addPage({ size: 'A4', layout: 'portrait', margins: { top: 60, bottom: 50, left: 72, right: 72 } });
  }
  isFirstDocumentPage = false;

  // 1. Centered Header Block (Times-Bold, 14pt)
  doc.fontSize(14).font('Times-Bold').fillColor('#000000');
  doc.text('PENGESAHAN BUKU CATATAN HARIAN', { align: 'center' });
  doc.moveDown(0.35);
  doc.text(`MINGGU KE - ${weekNum}`, { align: 'center' });
  doc.moveDown(0.35);
  doc.text('KULIAH KERJA NYATA', { align: 'center' });
  doc.moveDown(0.35);
  doc.text('UNIVERSITAS DIPONEGORO', { align: 'center' });
  doc.moveDown(0.35);

  const teamName = group.name ? group.name.toUpperCase() : '...........';
  doc.text(`TIM ${teamName}`, { align: 'center' });

  // 2. Indented Location & Student Metadata (Exact indentation without outer box)
  let curY = 255;
  const labelX = 190;
  const colonX = 255;
  const valX = 265;
  const rowH = 19;

  doc.font('Times-Roman').fontSize(11.5).fillColor('#000000');

  const locRows = [
    { label: 'Desa', val: group.village || '-' },
    { label: 'Kecamatan', val: group.district || '-' },
    { label: 'Kabupaten', val: group.regency || '-' },
    { label: 'Provinsi', val: group.province || 'Jawa Tengah' }
  ];

  locRows.forEach((item, idx) => {
    const yPos = curY + (idx * rowH);
    doc.text(item.label, labelX, yPos);
    doc.text(':', colonX, yPos);
    doc.text(item.val, valX, yPos);
  });

  curY += (locRows.length * rowH) + 32;

  // "Disusun Oleh" (centered around the block)
  doc.fontSize(11.5).font('Times-Roman').text('Disusun Oleh', 230, curY);
  curY += 24;

  const mhsRows = [
    { label: 'Nama', val: student.name || '-' },
    { label: 'NIM', val: student.nim || '-' },
    { label: 'Prodi', val: student.study_program?.name || student.prodi || '-' },
    { label: 'Fakultas', val: student.faculty?.name || student.fakultas || '-' }
  ];

  mhsRows.forEach((item, idx) => {
    const yPos = curY + (idx * rowH);
    doc.text(item.label, labelX, yPos);
    doc.text(':', colonX, yPos);
    doc.text(item.val, valX, yPos);
  });

  // 3. Signatures at Bottom (Page 1)
  const sigTopY = 570;
  const colLeftX = 72;
  const colRightX = 355;

  const kotaStr = `${group.regency || 'Semarang'}, ${getPrintDate()}`;
  doc.fontSize(11).font('Times-Roman').fillColor('#000000');
  doc.text(kotaStr, colRightX, sigTopY);

  const sigLabelY = sigTopY + 28;

  // Left: Dosen KKN
  doc.text('Disetujui Oleh :', colLeftX, sigLabelY);
  doc.text('Dosen KKN', colLeftX, sigLabelY + 14);

  doc.font('Times-Bold').text(dpl.name || '[Nama Dosen KKN]', colLeftX, sigLabelY + 95);
  doc.font('Times-Roman').text(`NIP. ${dpl.nip || '____________________'}`, colLeftX, sigLabelY + 110);

  // Right: Pelaksana Mahasiswa
  doc.text('Pelaksana', colRightX, sigLabelY);
  doc.text('Mahasiswa', colRightX, sigLabelY + 14);

  doc.font('Times-Bold').text(student.name || '[Nama Mahasiswa]', colRightX, sigLabelY + 95);
  doc.font('Times-Roman').text(`NIM. ${student.nim || '____________________'}`, colRightX, sigLabelY + 110);

  // =========================================================================
  // PAGE 2+: DAILY LOG SHEETS (Matches docs-source/logbook.pdf p.2)
  // =========================================================================
  const dailyList = weekLogs.length > 0 ? weekLogs : [
    {
      day_number: 1,
      date_formatted: '......................',
      activities: [],
      important_notes: '',
      image_base64: null
    }
  ];

  dailyList.forEach((log) => {
    doc.addPage({ size: 'A4', layout: 'portrait', margins: { top: 60, bottom: 50, left: 72, right: 72 } });

    const startY = 65;
    let currentY = startY;

    // -------------------------------------------------------------
    // ROW 1: ENCLOSED 3-COLUMN HEADER BLOCK
    // Col 1 (Logo): 85pt | Col 2 (Title): 216pt | Col 3 (Meta): 150pt
    // -------------------------------------------------------------
    const headerH = 75;
    const col1W = 85;
    const col2W = 216;
    const col3W = 150;

    doc.lineWidth(0.75).strokeColor('#000000');
    doc.rect(tableX, currentY, col1W, headerH).stroke();
    doc.rect(tableX + col1W, currentY, col2W, headerH).stroke();
    doc.rect(tableX + col1W + col2W, currentY, col3W, headerH).stroke();

    // Col 1: Logo
    if (logoPath) {
      const logoW = 54;
      const logoH = 58;
      const logoX = tableX + (col1W - logoW) / 2;
      const logoY = currentY + (headerH - logoH) / 2;
      try {
        doc.image(logoPath, logoX, logoY, { width: logoW, height: logoH });
      } catch (e) {
        // Skip if image format unsupported
      }
    }

    // Col 2: Title Block
    const titleX = tableX + col1W;
    const titleY = currentY + 11;
    doc.fontSize(10.5).font('Times-Bold').fillColor('#000000');
    doc.text('BUKU CATATAN HARIAN', titleX, titleY, { width: col2W, align: 'center' });
    doc.text('KULIAH KERJA NYATA', titleX, titleY + 14, { width: col2W, align: 'center' });
    doc.text('UNIVERSITAS DIPONEGORO', titleX, titleY + 28, { width: col2W, align: 'center' });
    const academicYear = period.year ? `${period.year}/${period.year + 1}` : '2025/2026';
    doc.text(academicYear, titleX, titleY + 42, { width: col2W, align: 'center' });

    // Col 3: Metadata (Hari ke, Hari, Tanggal)
    const metaX = tableX + col1W + col2W + 10;
    const metaY = currentY + 13;
    const dayName = log.date ? new Date(log.date).toLocaleDateString('id-ID', { weekday: 'long' }) : '.............';
    const dateStr = log.date_formatted || (log.date ? new Date(log.date).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '.............');

    doc.fontSize(9.5).font('Times-Roman').fillColor('#000000');
    doc.text(`Hari ke : ${log.day_number ?? '1'}`, metaX, metaY);
    doc.text(`Hari     : ${dayName}`, metaX, metaY + 16);
    doc.text(`Tanggal: ${dateStr}`, metaX, metaY + 32);

    currentY += headerH;

    // -------------------------------------------------------------
    // ROW 2: "A. Jadwal Kegiatan" FULL WIDTH BANNER
    // -------------------------------------------------------------
    const bannerH = 18;
    doc.rect(tableX, currentY, tableWidth, bannerH).stroke();
    doc.fontSize(10).font('Times-Roman').fillColor('#000000')
       .text('A. Jadwal Kegiatan', tableX + 8, currentY + 4);
    currentY += bannerH;

    // -------------------------------------------------------------
    // ROW 3: TABLE COLUMN HEADERS (No, Waktu, Kegiatan)
    // -------------------------------------------------------------
    const thH = 18;
    const colNoW = 28;
    const colWaktuW = 69;
    const colKegiatanW = 354;

    doc.rect(tableX, currentY, colNoW, thH).stroke();
    doc.rect(tableX + colNoW, currentY, colWaktuW, thH).stroke();
    doc.rect(tableX + colNoW + colWaktuW, currentY, colKegiatanW, thH).stroke();

    doc.fontSize(9.5).font('Times-Roman').fillColor('#000000');
    doc.text('No', tableX, currentY + 4, { width: colNoW, align: 'center' });
    doc.text('Waktu', tableX + colNoW, currentY + 4, { width: colWaktuW, align: 'center' });
    doc.text('Kegiatan', tableX + colNoW + colWaktuW + 6, currentY + 4, { width: colKegiatanW - 12, align: 'left' });

    currentY += thH;

    // -------------------------------------------------------------
    // ROWS 4+: FILLED ACTIVITY ROWS ONLY (No empty filler rows)
    // -------------------------------------------------------------
    const activities = log.activities || [];
    const defaultRowH = 18;

    if (activities.length === 0) {
      const emptyRowH = 22;
      doc.rect(tableX, currentY, colNoW, emptyRowH).stroke();
      doc.rect(tableX + colNoW, currentY, colWaktuW, emptyRowH).stroke();
      doc.rect(tableX + colNoW + colWaktuW, currentY, colKegiatanW, emptyRowH).stroke();

      doc.fontSize(9).font('Times-Italic').fillColor('#64748b');
      doc.text('-', tableX, currentY + 5, { width: colNoW, align: 'center' });
      doc.text('-', tableX + colNoW, currentY + 5, { width: colWaktuW, align: 'center' });
      doc.text('Belum ada aktivitas dicatat.', tableX + colNoW + colWaktuW + 6, currentY + 5, {
        width: colKegiatanW - 12,
        align: 'left'
      });
      currentY += emptyRowH;
    } else {
      activities.forEach((act, rIdx) => {
        let rowH = defaultRowH;
        if (act && act.activity_description) {
          const textH = doc.heightOfString(act.activity_description, { width: colKegiatanW - 12 });
          if (textH + 8 > rowH) rowH = textH + 8;
        }

        doc.rect(tableX, currentY, colNoW, rowH).stroke();
        doc.rect(tableX + colNoW, currentY, colWaktuW, rowH).stroke();
        doc.rect(tableX + colNoW + colWaktuW, currentY, colKegiatanW, rowH).stroke();

        doc.fontSize(9).font('Times-Roman').fillColor('#000000');
        doc.text(`${rIdx + 1}.`, tableX, currentY + 4, { width: colNoW, align: 'center' });

        const timeStr = (act.start_time || act.end_time) ? `${act.start_time || ''} - ${act.end_time || ''}` : '';
        doc.text(timeStr, tableX + colNoW, currentY + 4, { width: colWaktuW, align: 'center' });

        doc.text(act.activity_description || '-', tableX + colNoW + colWaktuW + 6, currentY + 4, {
          width: colKegiatanW - 12,
          align: 'left'
        });

        currentY += rowH;
      });
    }

    // -------------------------------------------------------------
    // ROW N: "B. Catatan Penting Harian" FULL WIDTH BANNER
    // -------------------------------------------------------------
    doc.rect(tableX, currentY, tableWidth, bannerH).stroke();
    doc.fontSize(10).font('Times-Roman').fillColor('#000000')
       .text('B. Catatan Penting Harian', tableX + 8, currentY + 4);
    currentY += bannerH;

    // -------------------------------------------------------------
    // ROW N+1: CATATAN PENTING HARIAN & ATTACHED IMAGE BOX
    // -------------------------------------------------------------
    let imgBuffer = null;
    if (log.image_base64) {
      try {
        const b64Data = log.image_base64.includes('base64,')
          ? log.image_base64.split('base64,')[1]
          : log.image_base64;
        imgBuffer = Buffer.from(b64Data, 'base64');
      } catch (e) {
        imgBuffer = null;
      }
    } else if (log.image_path && fs.existsSync(log.image_path)) {
      try {
        imgBuffer = fs.readFileSync(log.image_path);
      } catch (e) {
        imgBuffer = null;
      }
    }

    doc.fontSize(9.5).font('Times-Roman');
    const notesText = log.important_notes ? String(log.important_notes) : '';
    const textH = notesText ? doc.heightOfString(notesText, { width: tableWidth - 20, lineGap: 3 }) : 0;

    const imgMaxW = 320;
    const imgMaxH = 150;
    const imgSectionH = imgBuffer ? (imgMaxH + 15) : (log.image_url ? 20 : 0);

    const neededH = (textH > 0 ? textH + 12 : 0) + imgSectionH + 15;
    const notesBoxH = Math.max(100, neededH);

    // Draw outer box for notes & image attachment
    doc.rect(tableX, currentY, tableWidth, notesBoxH).stroke();

    let innerY = currentY + 8;

    // Render notes text
    if (notesText) {
      doc.fillColor('#000000').text(notesText, tableX + 10, innerY, {
        width: tableWidth - 20,
        lineGap: 3,
        align: 'justify'
      });
      innerY += textH + 10;
    }

    // Render image attachment inside notes box
    if (imgBuffer) {
      try {
        const imgX = tableX + (tableWidth - imgMaxW) / 2;
        doc.image(imgBuffer, imgX, innerY, {
          fit: [imgMaxW, imgMaxH],
          align: 'center',
          valign: 'center'
        });
        innerY += imgMaxH + 5;
      } catch (e) {
        if (log.image_url) {
          doc.fontSize(8.5).font('Times-Italic').fillColor('#2563eb')
             .text(`Lampiran Foto: ${log.image_url}`, tableX + 10, innerY, {
               width: tableWidth - 20,
               link: log.image_url,
               underline: true
             });
        }
      }
    } else if (log.image_url) {
      doc.fontSize(8.5).font('Times-Italic').fillColor('#2563eb')
         .text(`Lampiran Foto: ${log.image_url}`, tableX + 10, innerY, {
           width: tableWidth - 20,
           link: log.image_url,
           underline: true
         });
    }

    currentY += notesBoxH + 20;

    // -------------------------------------------------------------
    // SIGNATURE ON BOTTOM RIGHT OF DAILY SHEET (Page 2)
    // -------------------------------------------------------------
    const sigX = 355;
    const dailyDateStr = log.date
      ? `${group.regency || 'Semarang'}, ${new Date(log.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`
      : `${group.regency || 'Semarang'}, ${getPrintDate()}`;

    doc.fontSize(10.5).font('Times-Roman').fillColor('#000000');
    doc.text(dailyDateStr, sigX, currentY);
    currentY += 16;
    doc.text('Pelaksana', sigX, currentY);
    currentY += 14;
    doc.text('Mahasiswa', sigX, currentY);

    currentY += 55;
    doc.font('Times-Bold').text(student.name || '[Nama Mahasiswa]', sigX, currentY);
    currentY += 14;
    doc.font('Times-Roman').text(`NIM. ${student.nim || '...................'}`, sigX, currentY);
  });
});

doc.end();

writeStream.on('finish', () => {
  process.exit(0);
});

writeStream.on('error', (err) => {
  console.error('Write stream error:', err);
  process.exit(1);
});
