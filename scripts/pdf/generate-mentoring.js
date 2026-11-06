#!/usr/bin/env node

/**
 * PDFKit Generator for Buku Pembimbingan KKN (Mentoring Logs)
 * Exact format recreated from docs-source/pembimbingan.docx, pembimbingan.md & mentoring.pdf
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
  console.error('Usage: node generate-mentoring.js --input=<path> --output=<path>');
  process.exit(1);
}

const payload = JSON.parse(fs.readFileSync(inputPath, 'utf8'));

const doc = new PDFDocument({
  size: 'A4',
  layout: 'landscape',
  margins: { top: 40, bottom: 40, left: 38, right: 20 },
  autoFirstPage: true,
  bufferPages: true
});

const writeStream = fs.createWriteStream(outputPath);
doc.pipe(writeStream);

const student = payload.student || {};
const group = payload.group || {};
const period = payload.period || {};
const dpl = payload.dpl || {};
const groupedLogs = payload.groupedLogs || {};

// Exact 8 columns matching docs-source/mentoring.pdf and #04 docx (Total = 784 pt)
const columns = [
  { key: 'no', label: 'No', width: 25, align: 'center', headerAlign: 'center' },
  { key: 'tanggal', label: 'Tanggal', width: 65, align: 'center', headerAlign: 'center' },
  { key: 'kegiatan', label: 'Kegiatan', width: 155, align: 'left', headerAlign: 'center' },
  { key: 'uraian', label: 'Uraian & Hambatan', width: 170, align: 'left', headerAlign: 'center' },
  { key: 'solusi', label: 'Solusi / Saran dari Dosen KKN', width: 144, align: 'left', headerAlign: 'center' },
  { key: 'sasaran', label: 'Kelompok Sasaran', width: 60, align: 'left', headerAlign: 'center' },
  { key: 'jmlMhs', label: 'Jumlah Mahasiswa', width: 58, align: 'center', headerAlign: 'center' },
  { key: 'luaran', label: 'Luaran', width: 107, align: 'left', headerAlign: 'center' }
];

const tableX = 37.5;
const tableWidth = columns.reduce((sum, c) => sum + c.width, 0); // 784 pt
const headerH = 26;
const cellPadding = 4;

function formatDateIndo(dateStr) {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
  } catch (e) {
    return dateStr;
  }
}

function formatLongDateIndo(dateStr) {
  if (!dateStr && payload.printed_at) return payload.printed_at;
  try {
    const d = dateStr ? new Date(dateStr) : new Date();
    if (isNaN(d.getTime())) return dateStr || '';
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  } catch (e) {
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const now = new Date();
    return `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  }
}

function drawTableHeader(curY) {
  doc.lineWidth(0.5).strokeColor('#000000');
  let curX = tableX;

  columns.forEach((col) => {
    doc.rect(curX, curY, col.width, headerH).stroke();

    doc.fontSize(8.5).font('Times-Bold').fillColor('#000000');
    doc.text(col.label, curX + cellPadding, curY + 4, {
      width: col.width - (cellPadding * 2),
      align: col.headerAlign || 'center'
    });

    curX += col.width;
  });

  return curY + headerH;
}

const programGroups = Object.keys(groupedLogs);
const activeProgramKeys = programGroups.length > 0 ? programGroups : ['default'];

let isFirstSheet = true;

activeProgramKeys.forEach((progKey) => {
  const logEntries = groupedLogs[progKey] || [];
  const firstLog = logEntries[0] || {};
  const programType = firstLog.program_type_name || firstLog.program?.program_type?.name || 'Multidisiplin';
  const programTitle = firstLog.program_title || firstLog.program?.title || 'Program Kerja KKN';

  if (!isFirstSheet) {
    doc.addPage({ size: 'A4', layout: 'landscape', margins: { top: 40, bottom: 40, left: 38, right: 20 } });
  }
  isFirstSheet = false;

  // 1. TITLE
  doc.fontSize(14).font('Times-Bold').fillColor('#000000')
    .text('Buku Pembimbingan Kuliah Kerja Nyata Universitas Diponegoro', 0, 48, {
      width: 841.89,
      align: 'center'
    });

  // 2. JENIS PROGRAM
  const jenisY = 92;
  doc.fontSize(10.5).font('Times-Bold').fillColor('#000000')
    .text('Jenis Program : ', 72, jenisY, { continued: true });
  doc.font('Times-Roman').text(`${programType}`);

  // 3. STUDENT & LOCATION METADATA (Unboxed 2-column layout matching docx/mentoring.pdf)
  const metaY = 114;
  const lineH = 13;

  // Column 1: Student Details
  const c1LabelX = 72;
  const c1ColonX = 126;
  const c1ValX = 134;

  const leftMeta = [
    { label: 'Nama', val: student.name || '' },
    { label: 'NIM', val: student.nim || '' },
    { label: 'Prodi', val: student.study_program?.name || student.prodi || '' },
    { label: 'Fakultas', val: student.faculty?.name || student.fakultas || '' }
  ];

  leftMeta.forEach((item, idx) => {
    const yPos = metaY + (idx * lineH);
    doc.fontSize(9.5).font('Times-Roman').fillColor('#000000');
    doc.text(item.label, c1LabelX, yPos);
    doc.text(':', c1ColonX, yPos);
    doc.text(item.val, c1ValX, yPos, { width: 160 });
  });

  // Column 2: Location Details
  const c2LabelX = 300;
  const c2ColonX = 368;
  const c2ValX = 376;

  const rightMeta = [
    { label: 'Desa', val: group.village || '' },
    { label: 'Kecamatan', val: group.district || '' },
    { label: 'Kabupaten', val: group.regency || '' },
    { label: 'Provinsi', val: group.province || 'Jawa Tengah' }
  ];

  rightMeta.forEach((item, idx) => {
    const yPos = metaY + (idx * lineH);
    doc.fontSize(9.5).font('Times-Roman').fillColor('#000000');
    doc.text(item.label, c2LabelX, yPos);
    doc.text(':', c2ColonX, yPos);
    doc.text(item.val, c2ValX, yPos, { width: 200 });
  });

  // 4. JUDUL PROGRAM
  const titleY = metaY + (4 * lineH) + 12;
  doc.fontSize(10).font('Times-Bold').fillColor('#000000')
    .text('Judul Program : ', 72, titleY, { continued: true });
  doc.font('Times-Roman').text(programTitle);

  // 5. MENTORING LOGS TABLE
  let currentY = titleY + 22;
  currentY = drawTableHeader(currentY);

  // Filter actual filled logs (skip placeholder dummy records if empty)
  const actualLogs = logEntries.filter(l => l && (l.topic || l.date || l.activity_description));

  if (actualLogs.length === 0) {
    // 1 empty / placeholder row
    const emptyRowH = 22;
    doc.lineWidth(0.5).strokeColor('#000000');
    let curX = tableX;

    columns.forEach(col => {
      doc.rect(curX, currentY, col.width, emptyRowH).stroke();
      curX += col.width;
    });

    doc.fontSize(8.5).font('Times-Italic').fillColor('#64748b');
    doc.text('-', tableX, currentY + 5, { width: columns[0].width, align: 'center' });
    doc.text('-', tableX + columns[0].width, currentY + 5, { width: columns[1].width, align: 'center' });
    doc.text('Belum ada catatan pembimbingan.', tableX + columns[0].width + columns[1].width + 6, currentY + 5, {
      width: columns[2].width - 12,
      align: 'left'
    });

    currentY += emptyRowH;
  } else {
    // Render only actual filled rows
    actualLogs.forEach((log, idx) => {
      const rowData = {
        no: `${idx + 1}.`,
        tanggal: formatDateIndo(log.date),
        kegiatan: log.topic || log.activity_description || '-',
        uraian: log.discussion_summary || log.obstacle || '-',
        solusi: log.dpl_feedback || log.solution || '-',
        sasaran: log.target_group || log.target_audience || '-',
        jmlMhs: String(log.student_count || '1'),
        luaran: log.output || '-'
      };

      // Calculate dynamic row height
      doc.fontSize(8.5).font('Times-Roman');
      let maxCellH = 20;

      columns.forEach(col => {
        const text = rowData[col.key] || '';
        const h = doc.heightOfString(text, {
          width: col.width - (cellPadding * 2),
          lineGap: 1.5
        }) + (cellPadding * 2);
        if (h > maxCellH) maxCellH = h;
      });

      // Page break check (ensure space for signature block if last row)
      const isLastRow = idx === actualLogs.length - 1;
      const neededSpace = isLastRow ? maxCellH + 110 : maxCellH + 30;

      if (currentY + neededSpace > doc.page.height - 40) {
        doc.addPage({ size: 'A4', layout: 'landscape', margins: { top: 40, bottom: 40, left: 38, right: 20 } });
        currentY = 45;
        currentY = drawTableHeader(currentY);
        doc.fontSize(8.5).font('Times-Roman');
      }

      // Draw row cells
      doc.lineWidth(0.5).strokeColor('#000000');
      let curX = tableX;

      columns.forEach(col => {
        doc.rect(curX, currentY, col.width, maxCellH).stroke();

        const text = rowData[col.key] || '';
        doc.fillColor('#000000').text(text, curX + cellPadding, currentY + cellPadding + 1, {
          width: col.width - (cellPadding * 2),
          align: col.align || 'left',
          lineGap: 1.5
        });

        curX += col.width;
      });

      currentY += maxCellH;
    });
  }

  // 6. SIGNATURE BLOCK (Matching exact docx / mentoring.pdf coordinates)
  currentY += 24;

  if (currentY + 100 > doc.page.height - 40) {
    doc.addPage({ size: 'A4', layout: 'landscape', margins: { top: 40, bottom: 40, left: 38, right: 20 } });
    currentY = 50;
  }

  const sigLeftX = 77;   // Dosen KKN column
  const sigRightX = 426; // Pelaksana column

  const sigDateStr = `${group.regency || 'Kota Semarang'}, ${formatLongDateIndo()}`;

  doc.fontSize(10).font('Times-Roman').fillColor('#000000');

  // Date placed above Pelaksana
  doc.text(sigDateStr, sigRightX, currentY);
  currentY += 15;

  // Roles
  doc.text('Dosen KKN', sigLeftX, currentY);
  doc.text('Pelaksana', sigRightX, currentY);

  currentY += 58;

  // Names
  doc.font('Times-Bold').text(dpl.name || '[Nama Dosen KKN]', sigLeftX, currentY);
  doc.text(student.name || '[Nama Mahasiswa]', sigRightX, currentY);

  currentY += 14;

  // NIP / NIM
  doc.font('Times-Roman').text(`NIP. ${dpl.nip || '....................................'}`, sigLeftX, currentY);
  doc.text(`NIM. ${student.nim || '....................................'}`, sigRightX, currentY);
});

doc.end();

writeStream.on('finish', () => {
  process.exit(0);
});

writeStream.on('error', (err) => {
  console.error('Write stream error:', err);
  process.exit(1);
});
