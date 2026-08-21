<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 210mm 297mm;
            margin: 2.5cm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #fff; }
        .imagen-formato {
            display: block;
            width: 80%;
            margin: 2cm auto 0 auto;
        }
    </style>
</head>
<body>
    <img src="data:image/jpeg;base64,{{ $imagenBase64 }}" class="imagen-formato" alt="Formato Orden de Despacho">
</body>
</html>
