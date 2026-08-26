<?php require __DIR__ . "/vendor/autoload.php"; $parser = new Smalot\PdfParser\Parser(); $pdf = $parser->parseFile("C:/Users/BRYAN/Downloads/download-pdf.pdf"); echo $pdf->getText();
