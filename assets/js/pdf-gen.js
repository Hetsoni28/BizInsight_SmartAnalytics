// ============================================================
//  BizInsight — PDF Generation Module (pdf-gen.js)
//  Premium "World-Class" PDF generator for reports & analytics
// ============================================================

/**
 * Helper to determine if a string looks like a number/currency
 */
function isNumericString(str) {
    // Remove common currency symbols and commas, then check if it's a number
    const cleanStr = str.replace(/[^0-9.-]+/g, "");
    return !isNaN(parseFloat(cleanStr)) && isFinite(cleanStr) && cleanStr !== "";
}

/**
 * Core PDF builder with modern branded header
 * Returns { doc, y } so callers can add content
 */
function createPDFDoc(title, subtitle) {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        alert('PDF library is still loading. Please wait a moment and try again.');
        return null;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    const margin = 18;
    const pageW = doc.internal.pageSize.getWidth();

    // Brand Colors
    const primaryDark = [15, 23, 42];  // Slate 900
    const primaryBlue = [37, 99, 235]; // Blue 600
    const textMuted   = [100, 116, 139]; // Slate 500
    
    // Header Layout
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(22);
    doc.setTextColor(...primaryDark);
    doc.text('BizInsight SmartAnalytics', margin, 24);

    doc.setFontSize(14);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...primaryBlue);
    doc.text(title || 'Executive Business Report', margin, 32);

    doc.setFontSize(9);
    doc.setTextColor(...textMuted);
    const dateStr = new Date().toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
    doc.text('Generated on: ' + dateStr, margin, 38);

    // Sleek Accent Line
    doc.setDrawColor(226, 232, 240); // Slate 200
    doc.setLineWidth(0.5);
    doc.line(margin, 44, pageW - margin, 44);

    return { doc: doc, y: 54 };
}

/**
 * Add elegant footer to all pages
 */
function addPDFFooter(doc) {
    const pageW = doc.internal.pageSize.getWidth();
    const margin = 18;
    const textMuted = [148, 163, 184]; // Slate 400
    const totalPages = doc.internal.getNumberOfPages();

    for (let p = 1; p <= totalPages; p++) {
        doc.setPage(p);
        const pgH = doc.internal.pageSize.getHeight();
        
        // Footer line
        doc.setDrawColor(241, 245, 249); // Slate 100
        doc.setLineWidth(0.5);
        doc.line(margin, pgH - 16, pageW - margin, pgH - 16);

        // Footer Text
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(...textMuted);
        doc.text('BizInsight SmartAnalytics — Confidential Internal Document', margin, pgH - 10);
        doc.text(`Page ${p} of ${totalPages}`, pageW - margin, pgH - 10, { align: 'right' });
    }
}

/**
 * Add modern, minimalist KPI summary cards
 */
function addKPICards(doc, y, kpiData) {
    const pageW = doc.internal.pageSize.getWidth();
    const margin = 18;
    const textDark  = [15, 23, 42];
    const textMuted = [100, 116, 139];
    const borderCol = [226, 232, 240];

    // Section Title
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...textDark);
    doc.text('EXECUTIVE SUMMARY', margin, y);
    y += 8;

    const count = Math.min(kpiData.length, 4);
    const gap = 6;
    const kpiW = (pageW - (margin * 2) - (gap * (count - 1))) / count;

    kpiData.slice(0, 4).forEach(function (kpi, i) {
        const x = margin + (i * (kpiW + gap));
        
        // Very subtle border, no fill (clean look)
        doc.setDrawColor(...borderCol);
        doc.setLineWidth(0.2);
        doc.roundedRect(x, y, kpiW, 26, 2, 2, 'S');
        
        // Label (Muted, uppercase, tracked)
        doc.setFontSize(7);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(...textMuted);
        doc.text(kpi.label.toUpperCase(), x + 6, y + 9);
        
        // Value (Large, dark)
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(...textDark);
        // Clean up unsupported unicode (Rupee symbol = \u20B9)
        let kpiVal = String(kpi.value).replace(/\u20B9/g, 'Rs. ');
        // Truncate value if too long, or scale it
        doc.text(kpiVal, x + 6, y + 20);
    });

    return y + 36;
}

/**
 * Capture and embed charts as images
 */
function embedCharts(doc, y) {
    const canvases = document.querySelectorAll('canvas');
    if (canvases.length === 0) return y;

    const pageW = doc.internal.pageSize.getWidth();
    const margin = 18;
    const usableW = pageW - (margin * 2);

    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(15, 23, 42);
    
    // Page break check for charts
    if (y > 200) {
        doc.addPage();
        y = 20;
    }

    doc.text('VISUAL ANALYTICS', margin, y);
    y += 8;

    // Handle up to 2 charts side-by-side or stack them nicely
    const chartW = canvases.length > 1 ? (usableW - 6) / 2 : usableW;
    let currentX = margin;
    let maxHeight = 0;

    canvases.forEach((canvas, index) => {
        // Calculate proportional height
        const ratio = canvas.height / canvas.width;
        const chartH = chartW * ratio;

        // If it doesn't fit horizontally, move to next row
        if (index > 0 && currentX + chartW > pageW - margin) {
            y += maxHeight + 10;
            currentX = margin;
            maxHeight = 0;
            // Page break check
            if (y + chartH > 270) {
                doc.addPage();
                y = 20;
            }
        }

        // Add white background before capturing (useful for transparent charts)
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        const ctx = tempCanvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        ctx.drawImage(canvas, 0, 0);

        try {
            const imgData = tempCanvas.toDataURL('image/png', 1.0);
            
            // Draw subtle border around chart for crispness
            doc.setDrawColor(226, 232, 240);
            doc.setLineWidth(0.2);
            doc.rect(currentX, y, chartW, chartH, 'S');

            doc.addImage(imgData, 'PNG', currentX, y, chartW, chartH, undefined, 'FAST');
            
            currentX += chartW + 6;
            if (chartH > maxHeight) maxHeight = chartH;
        } catch (e) {
            console.warn("Could not export chart to PDF", e);
        }
    });

    return y + maxHeight + 14;
}

/**
 * Add a premium data table to the PDF
 */
function addPDFTable(doc, y, title, head, body) {
    const margin = 18;
    const textDark  = [15, 23, 42];
    const borderCol = [226, 232, 240];
    
    // Check if we need to clean up emoji from title
    let cleanTitle = title.trim();
    if(cleanTitle === 'Data Table') cleanTitle = 'DETAILED DATA REPORT';

    // Page break check
    if (y > 240) {
        doc.addPage();
        y = 20;
    }

    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...textDark);
    doc.text(cleanTitle.toUpperCase(), margin, y);
    y += 4;

    doc.autoTable({
        startY: y,
        margin: { left: margin, right: margin },
        head: [head],
        body: body,
        theme: 'plain', // Use plain theme to build custom minimal borders
        headStyles: {
            fillColor: [248, 250, 252], // Slate 50
            textColor: [71, 85, 105],   // Slate 600
            fontSize: 8,
            fontStyle: 'bold',
            halign: 'left',
            cellPadding: { top: 6, bottom: 6, left: 4, right: 4 },
            lineWidth: { bottom: 0.5 },
            lineColor: borderCol
        },
        bodyStyles: {
            fontSize: 9,
            textColor: textDark,
            cellPadding: { top: 5, bottom: 5, left: 4, right: 4 },
            lineWidth: { bottom: 0.1 },
            lineColor: [241, 245, 249]
        },
        alternateRowStyles: {
            fillColor: [255, 255, 255]
        },
        didParseCell: function(data) {
            // Right-align numbers/currencies for professional look
            if (data.section === 'body' && data.column.index > 0) {
                if (isNumericString(data.cell.raw)) {
                    data.cell.styles.halign = 'right';
                }
            }
            // Match header alignment with body
            if (data.section === 'head' && data.column.index > 0) {
                // Peek at first row to see if it's numeric
                if (body.length > 0 && isNumericString(body[0][data.column.index])) {
                    data.cell.styles.halign = 'right';
                }
            }
            
            // Fix Unicode Rupee symbol (\u20B9)
            if (typeof data.cell.raw === 'string') {
                let cleanText = data.cell.raw.trim();
                // jsPDF standard fonts don't support the ₹ symbol, so we convert it to Rs.
                cleanText = cleanText.replace(/\u20B9/g, 'Rs. ');
                data.cell.text = [cleanText];
            }
        }
    });

    return doc.lastAutoTable.finalY + 14;
}

/**
 * Save the PDF with standardized name
 */
function savePDF(doc, title) {
    addPDFFooter(doc);
    var cleanTitle = (title || 'Report');
    var fileName = cleanTitle.replace(/[^a-zA-Z0-9 ]/g, '').replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.pdf';
    doc.save(fileName);
    if (typeof showToast === 'function') {
        showToast('PDF downloaded: ' + fileName);
    }
}

/**
 * Generate a PDF from visible charts and tables on any page
 */
function generatePagePDF(title, kpiData) {
    try {
        var result = createPDFDoc(title);
        if (!result) return;
        var doc = result.doc;
        var y = result.y;

        // 1. Add KPIs if provided
        if (kpiData && kpiData.length) {
            y = addKPICards(doc, y, kpiData);
        }

        // 2. Embed Charts if any exist on the page
        y = embedCharts(doc, y);

        // 3. Find all data tables and embed them
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
