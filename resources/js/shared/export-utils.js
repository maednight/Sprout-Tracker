const sanitizeFileNamePart = (value) => {
    return String(value || '')
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
  }

const escapeHtml = (value) => {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
  }

const escapeCsvValue = (value) => {
    const stringValue = String(value ?? '')

    if (/[",\n]/.test(stringValue)) {
      return `"${stringValue.replace(/"/g, '""')}"`
    }

    return stringValue
  }

const resolveLogoUrl = () => {
    return `${window.location.origin}/projectassets/images/logo/sprout-logo.svg`
  }

const buildMetaItems = (subtitle) => {
    return String(subtitle || '')
      .split('|')
      .map((part) => part.trim())
      .filter(Boolean)
  }

const formatAmount = (value) => {
    return Number(value || 0).toLocaleString('en-PH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }

const buildSummaryItemsHtml = (summaryItems = []) => {
    return summaryItems
      .map((item) => {
        const tone = String(item.tone || '').trim().toLowerCase()
        const toneColor = tone === 'income'
          ? '#00d95f'
          : tone === 'expense'
          ? '#ff6f5d'
          : tone === 'savings'
          ? '#2d9af0'
          : '#00c957'

        return `<div class="sprout-export__summary-card">
          <div class="sprout-export__summary-label">${escapeHtml(item.label || '')}</div>
          <div class="sprout-export__summary-value" style="color:${toneColor}">${escapeHtml(formatAmount(item.value))}</div>
        </div>`
      })
      .join('')
  }

const resolveTypeClass = (value) => {
    const normalizedValue = String(value || '').trim().toLowerCase()

    if (normalizedValue === 'income') {
      return 'sprout-export__type sprout-export__type--income'
    }

    if (normalizedValue === 'expense') {
      return 'sprout-export__type sprout-export__type--expense'
    }

    if (normalizedValue === 'savings') {
      return 'sprout-export__type sprout-export__type--savings'
    }

    return ''
  }

const resolveTypeColor = (value) => {
    const normalizedValue = String(value || '').trim().toLowerCase()

    if (normalizedValue === 'income') {
      return '#00d95f'
    }

    if (normalizedValue === 'expense') {
      return '#ff6f5d'
    }

    if (normalizedValue === 'savings') {
      return '#2d9af0'
    }

    return '#111111'
  }

const buildHeaderCellStyle = () => {
    return [
      'background:#00c957',
      'color:#ffffff',
      'border:1px solid #9ed9b5',
      'padding:9px 10px',
      'text-align:left',
      'font-size:12px',
      'font-weight:700',
      '-webkit-print-color-adjust:exact',
      'print-color-adjust:exact'
    ].join(';')
  }

const buildBodyCellStyle = (index, value, rowIndex, alignments = []) => {
    const styles = [
      'background:#ffffff',
      'color:#111111',
      'border:1px solid #9ed9b5',
      'padding:9px 10px',
      'text-align:left',
      'font-size:12px',
      'vertical-align:top',
      '-webkit-print-color-adjust:exact',
      'print-color-adjust:exact'
    ]

    if (alignments[index] === 'right') {
      styles.push('text-align:right')
    }

    if (index === 2) {
      styles.push(`color:${resolveTypeColor(value)}`)
      styles.push('font-weight:700')
    }

    return styles.join(';')
  }

const downloadBlob = (blob, fileName) => {
    const objectUrl = window.URL.createObjectURL(blob)
    const anchor = document.createElement('a')

    anchor.href = objectUrl
    anchor.download = fileName
    document.body.appendChild(anchor)
    anchor.click()
    anchor.remove()

    window.setTimeout(() => {
      window.URL.revokeObjectURL(objectUrl)
    }, 1000)
  }

const buildFileBaseName = (parts) => {
    const sanitizedParts = parts
      .map((part) => sanitizeFileNamePart(part))
      .filter(Boolean)

    return sanitizedParts.length > 0 ? sanitizedParts.join('-') : 'export'
  }

const downloadCsv = ({ fileName, headers, rows }) => {
    const csvLines = [
      headers.map(escapeCsvValue).join(','),
      ...rows.map((row) => row.map(escapeCsvValue).join(','))
    ]

    downloadBlob(
      new Blob([`\uFEFF${csvLines.join('\n')}`], { type: 'text/csv;charset=utf-8;' }),
      fileName
    )
  }

const downloadExcel = ({ fileName, title, subtitle, summaryItems = [], headers, rows, alignments = [] }) => {
    const metaItems = buildMetaItems(subtitle)
    const summaryHtml = buildSummaryItemsHtml(summaryItems)
    const tableHeaders = headers
      .map((header) => `<th style="${buildHeaderCellStyle()}">${escapeHtml(header)}</th>`)
      .join('')

    const tableRows = rows
      .map((row, rowIndex) => {
        const cells = row
          .map((cell, index) => {
            return `<td style="${buildBodyCellStyle(index, cell, rowIndex, alignments)}">${escapeHtml(cell)}</td>`
          })
          .join('')
        return `<tr>${cells}</tr>`
      })
      .join('')

    const excelHtml = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; padding: 24px; color: #1f2933; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sprout-export__header { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #b7efc9; }
    .sprout-export__logo { width: 48px; height: 48px; object-fit: contain; flex: 0 0 48px; }
    .sprout-export__body { min-width: 0; }
    .sprout-export__eyebrow { margin: 0 0 4px; color: #00a34a; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
    h1 { margin: 0 0 10px; font-size: 24px; color: #008f45; line-height: 1.2; }
    .sprout-export__meta { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; }
    .sprout-export__meta-item { color: #1f7a43; background: #e8fff0; border: 1px solid #9ed9b5; border-radius: 999px; font-size: 11px; padding: 5px 10px; }
    .sprout-export__summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 0 0 16px; }
    .sprout-export__summary-card { border: 1px solid #9ed9b5; background: #ffffff; border-radius: 12px; padding: 10px 12px; }
    .sprout-export__summary-label { color: #5b6b62; font-size: 11px; margin-bottom: 4px; }
    .sprout-export__summary-value { font-size: 16px; font-weight: 700; }
    table { border-collapse: collapse; width: 100%; }
  </style>
</head>
<body>
  <div class="sprout-export__header">
    <img src="${escapeHtml(resolveLogoUrl())}" alt="Sprout Income Expense Tracker logo" class="sprout-export__logo">
    <div class="sprout-export__body">
      <div class="sprout-export__eyebrow">Sprout Income Expense Tracker</div>
      <h1>${escapeHtml(title || 'Sprout Report')}</h1>
      <div class="sprout-export__meta">
        ${metaItems.map((item) => `<span class="sprout-export__meta-item">${escapeHtml(item)}</span>`).join('')}
      </div>
    </div>
  </div>
  <div class="sprout-export__summary">${summaryHtml}</div>
  <table>
    <thead><tr>${tableHeaders}</tr></thead>
    <tbody>${tableRows}</tbody>
  </table>
</body>
</html>`

    downloadBlob(
      new Blob([excelHtml], { type: 'application/vnd.ms-excel;charset=utf-8;' }),
      fileName
    )
  }

const printPdf = ({ title, subtitle, summaryItems = [], headers, rows, alignments = [] }) => {
    const metaItems = buildMetaItems(subtitle)
    const summaryHtml = buildSummaryItemsHtml(summaryItems)
    const tableHeaders = headers
      .map((header) => `<th style="${buildHeaderCellStyle()}">${escapeHtml(header)}</th>`)
      .join('')

    const tableRows = rows
      .map((row, rowIndex) => {
        const cells = row
          .map((cell, index) => {
            return `<td style="${buildBodyCellStyle(index, cell, rowIndex, alignments)}">${escapeHtml(cell)}</td>`
          })
          .join('')
        return `<tr>${cells}</tr>`
      })
      .join('')

    const printHtml = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>${escapeHtml(title || 'Sprout Report')}</title>
  <style>
    @page { margin: 12mm; }
    body { font-family: Arial, sans-serif; padding: 0; color: #1f2933; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sprout-export { width: 100%; }
    .sprout-export__header { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #b7efc9; }
    .sprout-export__logo { width: 52px; height: 52px; object-fit: contain; flex: 0 0 52px; }
    .sprout-export__body { min-width: 0; }
    .sprout-export__eyebrow { margin: 0 0 4px; color: #00a34a; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
    h1 { margin: 0 0 10px; font-size: 24px; color: #008f45; line-height: 1.2; }
    .sprout-export__meta { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; }
    .sprout-export__meta-item { color: #1f7a43; background: #e8fff0; border: 1px solid #9ed9b5; border-radius: 999px; font-size: 11px; padding: 5px 10px; }
    .sprout-export__summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 0 0 16px; }
    .sprout-export__summary-card { border: 1px solid #9ed9b5; background: #ffffff; border-radius: 12px; padding: 10px 12px; }
    .sprout-export__summary-label { color: #5b6b62; font-size: 11px; margin-bottom: 4px; }
    .sprout-export__summary-value { font-size: 16px; font-weight: 700; }
    table { border-collapse: collapse; width: 100%; }
    @media print {
      html, body { margin: 0; }
    }
  </style>
</head>
<body>
  <div class="sprout-export">
    <div class="sprout-export__header">
      <img src="${escapeHtml(resolveLogoUrl())}" alt="Sprout Income Expense Tracker logo" class="sprout-export__logo">
      <div class="sprout-export__body">
        <div class="sprout-export__eyebrow">Sprout Income Expense Tracker</div>
        <h1>${escapeHtml(title || 'Sprout Report')}</h1>
      <div class="sprout-export__meta">
          ${metaItems.map((item) => `<span class="sprout-export__meta-item">${escapeHtml(item)}</span>`).join('')}
        </div>
      </div>
    </div>
    <div class="sprout-export__summary">${summaryHtml}</div>
    <table>
      <thead><tr>${tableHeaders}</tr></thead>
      <tbody>${tableRows}</tbody>
    </table>
  </div>
</body>
</html>`

    const printFrame = document.createElement('iframe')

    printFrame.setAttribute('aria-hidden', 'true')
    printFrame.style.position = 'fixed'
    printFrame.style.right = '0'
    printFrame.style.bottom = '0'
    printFrame.style.width = '0'
    printFrame.style.height = '0'
    printFrame.style.border = '0'

    document.body.appendChild(printFrame)

    const frameWindow = printFrame.contentWindow
    const frameDocument = printFrame.contentDocument || frameWindow?.document

    if (!frameWindow || !frameDocument) {
      printFrame.remove()
      return
    }

    frameDocument.open()
    frameDocument.write(printHtml)
    frameDocument.close()

    const triggerPrint = () => {
      frameWindow.focus()
      frameWindow.print()

      window.setTimeout(() => {
        printFrame.remove()
      }, 1000)
    }

    if (frameDocument.readyState === 'complete') {
      triggerPrint()
      return
    }

    printFrame.onload = triggerPrint
  }

export {
  buildFileBaseName,
  downloadCsv,
  downloadExcel,
  printPdf
}
