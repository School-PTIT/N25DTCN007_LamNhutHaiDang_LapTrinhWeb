$docPath = 'C:\Final web\N25DTCN007_LamNhutHaiDang_BaoCao.docx'
$pdfPath = 'C:\Final web\N25DTCN007_LamNhutHaiDang_BaoCao.pdf'

try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $doc = $word.Documents.Open($docPath)
    $doc.SaveAs([ref]$pdfPath, [ref]17) # 17 = wdFormatPDF
    $doc.Close()
    $word.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
    Write-Host "PDF EXPORT SUCCESSFUL!"
} catch {
    Write-Host "Export error: " $_.Exception.Message
}
