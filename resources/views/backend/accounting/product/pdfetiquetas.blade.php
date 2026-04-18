<!DOCTYPE html>
<html lang="es">
 <link rel="stylesheet" href="{{ asset('public/backend/assets/css/print_label.css') }}">
<head>
    <meta charset="UTF-8">
    <style>
        
    </style>
</head>
<body>
	<div class="col-md-12"><button class='btn btn-success' onclick="imprimirDiv()">Imprimir</button>
    <div class="grid-container" id="contenedor-qr">
	@foreach ($qrs as $qr)
    <div class="qr-card">
		<img src="data:image/png;base64,{{ $qr['codigo'] }}" class="qr-image">
            <div class="info">
                   <h4>{{ $qr['titulo'] }}</h4>
                   <p>{{ $qr['descripcion'] }}</p>
           </div>
    </div>
@endforeach
    </div>
</div>
    <script>
        
		
		function imprimirDiv() {
				const divContents = document.getElementById('contenedor-qr').innerHTML;
      var a = window.open("", "", "height=500, width=500");
      a.document.write(
        '<link rel="stylesheet" href="{{ asset('public/backend/assets/css/print_label.css') }}"><html>'
      );
      a.document.write("<body >");
      a.document.write(divContents);
      a.document.write("</body></html>");
      a.document.close();

      setTimeout(() => {
         a.print();
      }, 1000);
          
        }
    </script>
</body>
</html>
