// ============================================================
//  BizInsight — PDF Generation Module (pdf-gen.js)
//  Shared PDF generator for all pages: reports, dashboard,
//  analytics, sales
// ============================================================

/**
 * Core PDF builder with branded header/footer
 * Returns { doc, y } so callers can add content
 */
function createPDFDoc(title, subtitle) {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        alert('PDF library is still loading. Please wait a moment and try again.');
        return null;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    const pageW = doc.internal.pageSize.getWidth();
    const margin = 16;

    // Colors
    const blue  = [26, 127, 212];
    const white = [255, 255, 255];

    // Header bar
    doc.setFillColor(...blue);
    doc.rect(0, 0, pageW, 38, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.setTextColor(...white);
    doc.text('BizInsight SmartAnalytics', margin, 16);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...white);
    doc.text(title || 'Business Report', margin, 26);
    doc.setFontSize(9);
    doc.setTextColor(200, 223, 245);
    doc.text('Generated: ' + new Date().toLocaleDateString('en-IN', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }), margin, 33);

    return { doc: doc, y: 48 };
}

/**
 * Add branded footer to all pages
 */
function addPDFFooter(doc) {
    const pageW = doc.internal.pageSize.getWidth();
    const margin = 16;
    const blue = [26, 127, 212];
    const textMuted = [90, 122, 153];
    const totalPages = doc.internal.getNumberOfPages();

    for (let p = 1; p <= totalPages; p++) {
        doc.setPage(p);
        const pgH = doc.internal.pageSize.getHeight();
        doc.setDrawColor(...blue);
        doc.setLineWidth(0.5);
        doc.line(margin, pgH - 14, pageW - margin, pgH - 14);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(...textMuted);
        doc.text('BizInsight SmartAnalytics  |  Confidential', margin, pgH - 8);
        doc.text('Page ' + p + ' of ' + totalPages, pageW - margin, pgH - 8, { align: 'right' });
    }
}

/**
 * Add KPI summary cards to PDF
 * @param {Object} doc - jsPDF document
 * @param {number} y - current Y position
 * @param {Array} kpiData - [{label, value}, ...]
 * @returns {number} new Y position
 */
function addKPICards(doc, y, kpiData) {
    const pageW = doc.internal.pageSize.getWidth();
    const margin = 16;
    const blue = [26, 127, 212];
    const textDark = [26, 45, 66];
    const textMuted = [90, 122, 153];
    const bgLight = [240, 247, 255];

    doc.setFontSize(13);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...textDark);
    doc.text('Summary', margin, y);
    y += 6;

    const count = Math.min(kpiData.length, 4);
    const kpiW = (pageW - margin * 2 - (count - 1) * 4) / count;

    kpiData.slice(0, 4).forEach(function (kpi, i) {
        const x = margin + i * (kpiW + 4);
        doc.setFillColor(...bgLight);
        doc.roundedRect(x, y, kpiW, 24, 3, 3, 'F');
        doc.setDrawColor(...blue);
        doc.setLineWidth(0.3);
        doc.roundedRect(x, y, kpiW, 24, 3, 3, 'S');
        // Label
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(...textMuted);
        doc.text(kpi.label.toUpperCase(), x + kpiW / 2, y + 9, { align: 'center' });
        // Value
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(...textDark);
        doc.text(String(kpi.value), x + kpiW / 2, y + 19, { align: 'center' });
    });

    return y + 34;
}

/**
 * Add a data table to the PDF
 * @param {Object} doc - jsPDF document
 * @param {number} y - current Y position
 * @param {string} title - table section title
 * @param {Array} head - header columns
 * @param {Array} body - data rows
 * @returns {number} new Y position
 */
function addPDFTable(doc, y, title, head, body) {
    const margin = 16;
    const blue = [26, 127, 212];
    const textDark = [26, 45, 66];
    const bgLight = [240, 247, 255];
    const white = [255, 255, 255];

    // Page break check
    if (y > 220) {
        doc.addPage();
        y = 20;
    }

    doc.setFontSize(13);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...textDark);
    doc.text(title, margin, y);
    y += 3;

    doc.autoTable({
        startY: y,
        margin: { left: margin, right: margin },
        head: [head],
        body: body,
        theme: 'grid',
        headStyles: {
            fillColor: blue,
            textColor: white,
            fontSize: 9,
            fontStyle: 'bold',
            halign: 'left'
        },
        bodyStyles: {
            fontSize: 9,
            textColor: textDark,
            cellPadding: 4
        },
        alternateRowStyles: {
            fillColor: bgLight
        },
        styles: {
            lineColor: [197, 221, 244],
            lineWidth: 0.3
        }
    });

    return doc.lastAutoTable.finalY + 12;
}

/**
 * Save the PDF with standardized name
 */
function savePDF(doc, title) {
    addPDFFooter(doc);
    var fileName = (title || 'Report').replace(/[^a-zA-Z0-9 ]/g, '').replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.pdf';
    doc.save(fileName);
    if (typeof showToast === 'function') {
        showToast('PDF downloaded: ' + fileName);
    }
}

/**
 * Generate a PDF from visible table on any page (generic)
 * Reads the first .data-table on the page and exports it
 */
function generatePagePDF(title, kpiData) {
    try {
        var result = createPDFDoc(title);
        if (!result) return;
        var doc = result.doc;
        var y = result.y;

        // Add KPIs if provided
        if (kpiData && kpiData.length) {
            y = addKPICards(doc, y, kpiData);
        }

        // Find all data tables on the page
        var tables = document.querySelectorAll('.data-table');
        tables.forEach(function (table, tIdx) {
            var thead = table.querySelector('thead');
            var tbody = table.querySelector('tbody');
            if (!thead || !tbody) return;

            // Get title from preceding card-title
            var card = table.closest('.card');
            var tableTitle = 'Data Table';
            if (card) {
                var ct = card.querySelector('.card-title');
                if (ct) tableTitle = ct.textContent.trim();
            }

            // Parse headers — skip "Actions" column
            var headCells = thead.querySelectorAll('th');
            var head = [];
            var skipCols = [];
            headCells.forEach(function (th, idx) {
                var txt = th.textContent.trim();
                if (txt.toLowerCase() === 'actions' || txt.toLowerCase() === 'action') {
                    skipCols.push(idx);
                } else {
                    head.push(txt);
                }
            });

            // Parse body rows
            var rows = tbody.querySelectorAll('tr');
            var body = [];
            rows.forEach(function (tr) {
                var cells = tr.querySelectorAll('td');
                var row = [];
                cells.forEach(function (td, idx) {
                    if (skipCols.indexOf(idx) === -1) {
                        row.push(td.textContent.trim());
                    }
                });
                if (row.length) body.push(row);
            });

            if (head.length && body.length) {
                y = addPDFTable(doc, y, tableTitle, head, body);
            }
        });

        savePDF(doc, title);
    } catch (err) {
        console.error('PDF Generation Error:', err);
        alert('Error generating PDF: ' + err.message);
    }
}
