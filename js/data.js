// ===============================
// BASE DE DATOS DE MOTOS
// ===============================


const motos = [

{
id:1,
nombre:"Bajaj CT 125",
marca:"Bajaj",
precio:6500,
precioOferta:5990,
tipo:"trabajo",
imagen:"./assets/images/Bajaj CT 125.jpg",

descripcion:"La Bajaj CT 125 es la opción perfecta para quienes necesitan una motocicleta confiable y económica. Con su motor de 125 cc, ofrece un excelente desempeño en ciudad y para trabajo diario. Su consumo de combustible es muy bajo, lo que la hace ideal para trabajadores y repartidores. Diseñada con una estructura resistente, es perfecta para quienes necesitan durabilidad y bajo mantenimiento. El tanque de 10 litros le permite realizar jornadas completas sin necesidad de recargar constantemente.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"125 cc",
potencia:"10 HP",
peso:"115 kg",
transmision:"5 velocidades",
tanque:"10 litros",
frenos:"Tambor",
suspension:"Telescópica"
}

},


{
id:2,
nombre:"Bajaj Dominar 400",
marca:"Bajaj",
precio:18000,
precioOferta:16990,
tipo:"deportiva",
imagen:"./assets/images/Bajaj Dominar 400.jpg",

descripcion:"La Bajaj Dominar 400 es una motocicleta deportiva de clase media que combina potencia, estilo y seguridad. Con su motor monocilíndrico de 373 cc y 40 HP, ofrece un desempeño deportivo excepcional tanto en ciudad como en carretera. Equipada con frenos ABS, garantiza una frenada segura incluso en condiciones de emergencia. Su chasis robusto y suspensión de calidad proporcionan estabilidad en cualquier tipo de camino. Ideal para motociclistas que buscan una moto versátil con carácter deportivo.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"373 cc",
potencia:"40 HP",
peso:"184 kg",
transmision:"6 velocidades",
tanque:"13 litros",
frenos:"ABS",
suspension:"Telescópica"
}

},


{
id:3,
nombre:"Honda CB 190",
marca:"Honda",
precio:12000,
precioOferta:10800,
tipo:"deportiva",
imagen:"./assets/images/cb190.jpg",

descripcion:"La Honda CB 190 representa la excelencia en el segmento de motos deportivas de clase media. Con su motor de 184 cc y 15 HP, brinda un rendimiento ágil y eficiente. Su diseño moderno y agresivo refleja la identidad deportiva de Honda. Los frenos de disco delantero garantizan un control seguro, mientras que su estructura ligera facilita la maniobrabilidad urbana. Perfecta para principiantes y experimentados que buscan una moto confiable y de bajo mantenimiento.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"184 cc",
potencia:"15 HP",
peso:"140 kg",
transmision:"5 velocidades",
tanque:"12 litros",
frenos:"Disco",
suspension:"Telescópica"
}

},


{
id:4,
nombre:"Honda CB190R",
marca:"Honda",
precio:13500,
precioOferta:12500,
tipo:"deportiva",
imagen:"./assets/images/Honda CB190R.jpg",

descripcion:"La Honda CB190R es la versión Naked de la exitosa familia CB190 de Honda. Esta motocicleta combina un diseño agresivo y sin carenado con un motor de 184 cc que entrega 16 HP de potencia constante. Sistema de frenos ABS en ambas ruedas proporciona máxima seguridad en cualquier condición de conducción. Su posición de conducción más erguida la hace cómoda para viajes largos sin sacrificar la adrenalina en las curvas. Excelente relación entre precio, calidad y desempeño.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"184 cc",
potencia:"16 HP",
peso:"142 kg",
transmision:"5 velocidades",
tanque:"12 litros",
frenos:"ABS",
suspension:"Telescópica"
}

},


{
id:5,
nombre:"Honda CRF300L",
marca:"Honda",
precio:25000,
precioOferta:22500,
tipo:"offroad",
imagen:"./assets/images/Honda CRF300L.jpg",

descripcion:"La Honda CRF300L es una aventurera versátil diseñada para conquistar cualquier tipo de terreno. Con su motor de 286 cc generando 27 HP, ofrece suficiente potencia para enfrentar caminos rusticos y montañosos. Su suspensión de largo recorrido absorbe los impactos de caminos irregulares proporcionando comodidad y control. Ideal para viajes de aventura, exploradores de rutas alternas y quienes buscan escapar de la rutina urbana. Chasis liviano y maniobrabilidad excepcional la hacen una compañera perfecta para cualquier viaje.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"286 cc",
potencia:"27 HP",
peso:"138 kg",
transmision:"6 velocidades",
tanque:"7.8 litros",
frenos:"Disco",
suspension:"Largo recorrido"
}

},


{
id:6,
nombre:"Honda GL125",
marca:"Honda",
precio:5500,
precioOferta:4950,
tipo:"trabajo",
imagen:"./assets/images/Honda GL125.jpg",

descripcion:"La Honda GL125 es una motocicleta de trabajo versátil y extremadamente confiable. Conocida por su durabilidad legendaria en todo el mundo, esta moto de 125 cc es perfecta para repartidores, trabajadores y usuarios urbanos que necesitan economía de combustible superior. Su motor desarrolla 11 HP de forma consistente y eficiente. Diseño robusto y simple facilita el mantenimiento y reparaciones. Tanque de 10 litros permite jornadas completas sin necesidad de recargar. Una inversión inteligente para quienes valoran la confiabilidad.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"125 cc",
potencia:"11 HP",
peso:"115 kg",
transmision:"5 velocidades",
tanque:"10 litros",
frenos:"Tambor",
suspension:"Telescópica"
}

},


{
id:7,
nombre:"Honda Navi",
marca:"Honda",
precio:4800,
tipo:"urbana",
imagen:"./assets/images/Honda Navi.jpg",

descripcion:"La Honda Navi es la solución perfecta para la movilidad urbana compacta. Este scooter innovador de 110 cc combina funcionalidad con un diseño adorable y retro-moderno. Con transmisión automática, es ideal para principiantes y usuarios que prefieren conducción sin cambios de velocidad. Su tamaño compacto permite estacionar en cualquier espacio reducido, mientras que su motor de 8 HP proporciona suficiente potencia para movimientos ágiles en ciudad. Consumo de combustible ultra eficiente y bajo mantenimiento la hacen perfecta para estudiantes y profesionales urbanos.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"110 cc",
potencia:"8 HP",
peso:"101 kg",
transmision:"Automática",
tanque:"3.5 litros",
frenos:"Tambor",
suspension:"Telescópica"
}

},


{
id:8,
nombre:"Honda NX500",
marca:"Honda",
precio:28000,
tipo:"adventure",
imagen:"./assets/images/Honda NX500.jpg",

descripcion:"La Honda NX500 es una motocicleta adventure de clase media-alta diseñada para viajes largos y exploración. Con su motor bicilíndrico de 471 cc y 47 HP, ofrece potencia constante en cualquier terreno. Su tanque grande de 17 litros permite recorrer largas distancias entre recargas, ideal para aventuras en ruta. Sistema ABS avanzado garantiza máxima seguridad incluso en superficies mojadas o irregulares. Suspensión telescópica con buen recorrido absorbe impactos de caminos variados. Posición de conducción erguida proporciona comodidad en viajes extendidos. Perfecta para exploradores y amantes del viaje en motocicleta.",

especificaciones:{
motor:"Bicilíndrico",
cilindrada:"471 cc",
potencia:"47 HP",
peso:"196 kg",
transmision:"6 velocidades",
tanque:"17 litros",
frenos:"ABS",
suspension:"Telescópica"
}

},


{
id:9,
nombre:"Honda XBlade 160",
marca:"Honda",
precio:10500,
tipo:"deportiva",
imagen:"./assets/images/Honda X-Blade 160.jpg",

descripcion:"La Honda X-Blade 160 es una motocicleta deportiva compacta que combina agresividad visual con practicidad urbana. Su motor de 160 cc genera 13 HP de potencia pura, proporcionando aceleración emocionante sin ser excesiva. Consumo de combustible muy bajo la hace económica de mantener día a día. Diseño deportivo moderno con líneas agresivas la diferencia de la competencia. Frenos de disco delantero garantizan frenadas seguras y precisas. Ideal para conductores jóvenes, repartidores deportivos y entusiastas que buscan una moto ágil y versátil.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"160 cc",
potencia:"13 HP",
peso:"140 kg",
transmision:"5 velocidades",
tanque:"12 litros",
frenos:"Disco",
suspension:"Telescópica"
}

},


{
id:10,
nombre:"Honda XR150L",
marca:"Honda",
precio:9800,
tipo:"offroad",
imagen:"./assets/images/Honda XR150L.jpg",

descripcion:"La Honda XR150L es la moto offroad más versátil y práctica del mercado. Diseñada para ser dual purpose auténtica, se desempeña perfectamente tanto en carreteras como en caminos rurales. Su motor de 149 cc y 12 HP proporciona potencia suficiente para cualquier terreno. Suspensión con largo recorrido absorbe impactos de caminos ásperos. Ruedas con tacos proporcionan tracción excepcional en tierra y grava. Estructura simple y confiable hace el mantenimiento muy económico. Perfecta para mochileros, exploradores rurales y aventureros de fin de semana.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"149 cc",
potencia:"12 HP",
peso:"129 kg",
transmision:"5 velocidades",
tanque:"12 litros",
frenos:"Tambor",
suspension:"Largo recorrido"
}

},
{

id:11,
nombre:"Kawasaki Ninja 400",
marca:"Kawasaki",
precio:32000,
tipo:"deportiva",
imagen:"./assets/images/Kawasaki Ninja 400.jpg",

descripcion:"La Kawasaki Ninja 400 es una máquina deportiva pura que trae el ADN de la legendaria familia Ninja a una cilindrada accesible. Con su motor bicilíndrico paralelo de 399 cc y 45 HP, ofrece desempeño deportivo auténtico. Diseño aerodinámico agresivo con carenado completo reduce resistencia del viento a alta velocidad. Sistema de frenos ABS de doble canal proporciona máxima seguridad en frenadas de emergencia. Suspensión deportiva precisamente calibrada permite conducción en circuito. Perfecta para principiantes experimentados y entusiastas de las curvas que buscan emociones auténticas.",

especificaciones:{
motor:"Bicilíndrico paralelo",
cilindrada:"399 cc",
potencia:"45 HP",
peso:"168 kg",
transmision:"6 velocidades",
tanque:"14 litros",
frenos:"ABS doble canal",
suspension:"Horquilla telescópica"
}

},


{
id:12,
nombre:"Kawasaki Versys-X 300",
marca:"Kawasaki",
precio:26000,
tipo:"adventure",
imagen:"./assets/images/Kawasaki Versys-X 300.jpg",

descripcion:"La Kawasaki Versys-X 300 es una moto adventure compacta versátil para viajeros modernos. Su motor bicilíndrico paralelo de 296 cc y 39 HP ofrece potencia equilibrada para ciudad y carretera. Posición de conducción sport-tourer proporciona comodidad en viajes largos manteniendo dinamismo en curvas. Tanque de 17 litros permite recorrer distancias significativas entre recargas. Sistema ABS avanzado aumenta la seguridad en cualquier condición climática. Suspensión con largo recorrido maneja bien caminos irregulares. Diseño modular y práctico facilita ajustes personalizados. Ideal para aventureros urbanos que buscan versatilidad real.",

especificaciones:{
motor:"Bicilíndrico paralelo",
cilindrada:"296 cc",
potencia:"39 HP",
peso:"184 kg",
transmision:"6 velocidades",
tanque:"17 litros",
frenos:"ABS",
suspension:"Largo recorrido"
}

},


{
id:13,
nombre:"Kawasaki 125",
marca:"Kawasaki",
precio:9000,
tipo:"urbana",
imagen:"./assets/images/kawasaki125.jpg",

descripcion:"La Kawasaki 125 es una motocicleta urbana de cuatro tiempos diseñada para desplazamientos eficientes y económicos. Con su motor de 125 cc y 11 HP, proporciona rendimiento consistente en tráfico urbano y viajes cortos. Su consumo de combustible es ultra eficiente, ideal para economizar en gasolina durante el día. Diseño compacto y ligero facilita el manejo y estacionamiento en espacios reducidos. Estructura confiable y mantenimiento sencillo la convierten en una opción inteligente para usuarios que buscan practicidad sin sacrificar confort. Sistema de frenos de tambor estable y predecible en conducción urbana.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"125 cc",
potencia:"11 HP",
peso:"110 kg",
transmision:"5 velocidades",
tanque:"10 litros",
frenos:"Tambor",
suspension:"Telescópica"
}

},


{
id:14,
nombre:"KTM Adventure 390",
marca:"KTM",
precio:29000,
tipo:"adventure",
imagen:"./assets/images/KTM Adventure 390.jpg",

descripcion:"La KTM Adventure 390 es una motocicleta adventure versátil diseñada para conquistar cualquier terreno con tecnología de punta. Su motor monocilíndrico DOHC de 373 cc y 43 HP proporciona potencia ágil y responsiva. Suspensión WP regulable permite ajustar el setup según el tipo de terreno y preferencia del piloto. Sistema de frenos ABS Bosch avanzado garantiza máxima seguridad en frenadas de emergencia en superficies variadas. Posición de conducción erguida proporciona comodidad en jornadas largas. Tanque de 14.5 litros permite aventuras en rutas remotas sin preocupación. Perfecta para exploradores de rutas alternas y aventureros que demandan lo mejor de la tecnología.",

especificaciones:{
motor:"Monocilíndrico DOHC",
cilindrada:"373 cc",
potencia:"43 HP",
peso:"172 kg",
transmision:"6 velocidades",
tanque:"14.5 litros",
frenos:"ABS Bosch",
suspension:"WP regulable"
}

},


{
id:15,
nombre:"KTM Duke 200",
marca:"KTM",
precio:14000,
tipo:"deportiva",
imagen:"./assets/images/KTM Duke 200.jpg",

descripcion:"La KTM Duke 200 es una naked deportiva compacta que representa lo mejor de la ingeniería austríaca en una cilindrada accesible. Su motor monocilíndrico DOHC de 199 cc y 25 HP proporciona aceleración emocionante y respuesta instantánea. Peso ultra ligero de 134 kg facilita la maniobrabilidad en curvas cerradas y tráfico urbano. Frenos de disco ABS garantizan frenadas seguras y precisas incluso en condiciones adversas. Suspensión WP telescópica cuidadosamente calibrada absorbe irregularidades del pavimento. Diseño agresivo con líneas modernas refleja el ADN deportivo de KTM. Ideal para motociclistas jóvenes que buscan adrenalina y control en sus desplazamientos.",

especificaciones:{
motor:"Monocilíndrico DOHC",
cilindrada:"199 cc",
potencia:"25 HP",
peso:"134 kg",
transmision:"6 velocidades",
tanque:"13.4 litros",
frenos:"Disco ABS",
suspension:"WP telescópica"
}

},


{
id:16,
nombre:"KTM 250",
marca:"KTM",
precio:17000,
tipo:"deportiva",
imagen:"./assets/images/ktm250.jpg",

descripcion:"La KTM 250 es una motocicleta deportiva que equilibra perfectamente potencia, rendimiento y comodidad para conducción urbana y en carretera. Su motor monocilíndrico de 250 cc y 30 HP entrega potencia consistente en todo el rango de revoluciones. Peso manejable de 147 kg permite maniobras ágiles sin esfuerzo excesivo. Suspensión WP de calidad profesional absorbe irregularidades del terreno proporcionando confort en viajes largos. Sistema de frenos ABS avanzado maximiza la seguridad en cualquier condición climática. Transmisión de 6 velocidades permite optimizar el consumo de combustible. Posición de conducción deportiva manteniendo cierto confort para uso diario. Ideal para conductores que buscan un punto medio entre deportividad y practicidad.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"250 cc",
potencia:"30 HP",
peso:"147 kg",
transmision:"6 velocidades",
tanque:"13 litros",
frenos:"ABS",
suspension:"WP"
}

},
{

id:17,
nombre:"Yamaha MT-15",
marca:"Yamaha",
precio:13500,
tipo:"deportiva",
imagen:"./assets/images/mt15.jpg",

descripcion:"La Yamaha MT-15 es una naked deportiva compacta que combina diseño agresivo con eficiencia urbana excepcional. Su motor monocilíndrico SOHC de 155 cc y 18.5 HP proporciona potencia ágil perfecta para ciudad. Peso ligero de 141 kg facilita el manejo en tráfico congestionado. Horquilla invertida proporciona estabilidad superior comparada con competidores en su segmento. Sistema de frenos ABS garantiza frenadas seguras incluso en superficies mojadas. Consumo de combustible ultra eficiente mantiene gastos de operación bajos. Transmisión de 6 velocidades permite alcanzar buenas velocidades en carretera. Diseño moderno con líneas agresivas la diferencia de marcas competidoras. Perfecta para motociclistas jóvenes y profesionales que valoran la practicidad y el estilo.",

especificaciones:{
motor:"Monocilíndrico SOHC",
cilindrada:"155 cc",
potencia:"18.5 HP",
peso:"141 kg",
transmision:"6 velocidades",
tanque:"10 litros",
frenos:"ABS",
suspension:"Horquilla invertida"
}

},


{
id:18,
nombre:"Yamaha FZ25",
marca:"Yamaha",
precio:12500,
tipo:"deportiva",
imagen:"./assets/images/Yamaha FZ25.jpg",

descripcion:"La Yamaha FZ25 es una motocicleta deportiva urbana que ofrece un balance excepcional entre comodidad y rendimiento. Su motor monocilíndrico de 249 cc y 20 HP proporciona potencia constante para conducción variada. Posición de conducción erguida reduce fatiga en desplazamientos urbanos extensos. Tanque amplio de 14 litros permite viajes más largos sin necesidad de recargar con frecuencia. Frenos de disco delantero proporcionan control seguro en frenadas de emergencia. Suspensión telescópica estable absorbe irregularidades del pavimento urbano. Consumo de combustible moderado mantiene costos de operación accesibles. Chasis robusto garantiza durabilidad a largo plazo. Ideal para conductores que necesitan practicidad diaria sin sacrificar la emoción de una moto deportiva.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"249 cc",
potencia:"20 HP",
peso:"148 kg",
transmision:"5 velocidades",
tanque:"14 litros",
frenos:"Disco",
suspension:"Telescópica"
}

},


{
id:19,
nombre:"Yamaha MT-03",
marca:"Yamaha",
precio:22000,
tipo:"deportiva",
imagen:"./assets/images/Yamaha MT-03.jpg",

descripcion:"La Yamaha MT-03 es una naked deportiva que trae auténtico desempeño con su motor bicilíndrico paralelo de 321 cc y 42 HP. Este motor desarrolla un carácter deportivo puro con entrega linear de potencia en todo el rango. Horquilla invertida proporciona precisión de dirección y estabilidad excepcionales. Sistema de frenos ABS avanzado aumenta la confianza en frenadas agresivas. Suspensión deportiva calibrada permite maniobras precisas en carreteras sinuosas. Posición de conducción deportiva pero manejable facilita el aprendizaje de técnicas avanzadas. Peso equilibrado de 168 kg la hace ágil sin ser exigente. Transmisión de 6 velocidades optimiza el desempeño en cada condición. Perfecta para motociclistas experimentados que buscan una naked pura con tecnología moderna de seguridad.",

especificaciones:{
motor:"Bicilíndrico paralelo",
cilindrada:"321 cc",
potencia:"42 HP",
peso:"168 kg",
transmision:"6 velocidades",
tanque:"14 litros",
frenos:"ABS",
suspension:"Invertida"
}

},


{
id:20,
nombre:"Yamaha NMAX",
marca:"Yamaha",
precio:11500,
tipo:"urbana",
imagen:"./assets/images/Yamaha NMAX.jpg",

descripcion:"La Yamaha NMAX es un scooter urbano premium que combina tecnología moderna con comodidad y eficiencia superior. Su motor monocilíndrico de 155 cc y 15 HP proporciona aceleración ágil en tráfico urbano congestionado. Transmisión automática CVT elimina la necesidad de cambios manuales proporcionando experiencia de conducción relajada. Plataforma amplia permite conducir con los pies cómodos todo el tiempo. Sistema de frenos ABS avanzado garantiza máxima seguridad incluso en superficies mojadas. Diseño aerodinámico moderno con LED lights proporciona aspecto premium. Compartimento de almacenamiento bajo asiento facilita guardar objetos personales. Suspensión telescópica absorbe baches del pavimento urbano. Consumo de combustible ultra eficiente entre los mejores del mercado. Ideal para profesionales urbanos que valoran comodidad, seguridad y economía.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"155 cc",
potencia:"15 HP",
peso:"131 kg",
transmision:"Automática CVT",
tanque:"7.1 litros",
frenos:"ABS",
suspension:"Telescópica"
}

},


{
id:21,
nombre:"Yamaha R7",
marca:"Yamaha",
precio:35000,
tipo:"deportiva",
imagen:"./assets/images/Yamaha R7.jpg",

descripcion:"La Yamaha R7 es una motocicleta deportiva de alto rendimiento que trae tecnología de competición a la calle. Su motor bicilíndrico CP2 de 689 cc y 73 HP desarrolla aceleración brutal y respuesta instantánea del acelerador. Chasis derivado de tecnología de carreras proporciona manejo precisión de circuito. Horquilla invertida KYB y suspensión trasera de alta gama absorben impactos manteniendo contacto con el pavimento. Sistema de frenos ABS deportivo avanzado permite frenadas limite sin miedo al bloqueo. Carenado aerodinámico completo reduce resistencia del viento a altas velocidades. Posición de conducción baja y agresiva evoca el espíritu deportivo puro. Luces LED modernas mejoran visibilidad nocturna. Transmisión de 6 velocidades optimiza potencia en cada marcha. Perfecta para pilotos experimentados que buscan auténtico desempeño de carrera en sus aventuras de fin de semana.",

especificaciones:{
motor:"Bicilíndrico CP2",
cilindrada:"689 cc",
potencia:"73 HP",
peso:"188 kg",
transmision:"6 velocidades",
tanque:"13 litros",
frenos:"ABS deportivo",
suspension:"Invertida KYB"
}

},


{
id:22,
nombre:"Yamaha Tenere 700",
marca:"Yamaha",
precio:30000,
tipo:"adventure",
imagen:"./assets/images/Yamaha Tenere 700.jpg",

descripcion:"La Yamaha Tenere 700 es una adventure de clase alta preparada para viajes épicos en terrenos extremos. Su motor bicilíndrico CP2 de 689 cc y 72 HP ofrece potencia constante desde ciudad hasta caminos de montaña. Tanque extra grande de 16 litros permite recorrer más de 500 km entre recargas en rutas remotas. Suspensión de largo recorrido con amortiguadores de calidad absorbe golpes severos de caminos ásperos. Sistema de frenos ABS desconectable permite técnicas offroad avanzadas cuando es necesario. Posición de conducción erguida proporciona comodidad excepcional en viajes de múltiples días. Chassis robusto construido para durabilidad extrema en condiciones adversas. Protecciones de motor y tanque protegen componentes vitales contra impactos. Perfecta para aventureros serios que buscan conquistar rutas legendarias de América Latina.",

especificaciones:{
motor:"Bicilíndrico CP2",
cilindrada:"689 cc",
potencia:"72 HP",
peso:"205 kg",
transmision:"6 velocidades",
tanque:"16 litros",
frenos:"ABS desconectable",
suspension:"Largo recorrido"
}

},


{
id:23,
nombre:"Yamaha XTZ125",
marca:"Yamaha",
precio:8500,
tipo:"offroad",
imagen:"./assets/images/Yamaha XTZ125.jpg",

descripcion:"La Yamaha XTZ125 es una motocicleta doble propósito confiable diseñada para conquistar ciudad y caminos rurales. Su motor monocilíndrico de 125 cc y 12 HP proporciona potencia suficiente para cualquier condición de terreno. Suspensión con largo recorrido absorbe impactos severos de caminos rusticos sin sacrificar control. Ruedas con tacos proporcionan tracción excepcional en tierra, grava y superficies mojadas. Estructura resistente construida para durabilidad extrema en condiciones difficiles. Tanque de 11 litros permite jornadas largas en zonas remotas. Frenos de tambor predecibles brindan control consistente en cualquier terreno. Consumo de combustible eficiente reduce gastos de operación. Sistema de encendido confiable funciona incluso en condiciones de humedad extrema. Ideal para campesinos, exploradores rurales y aventureros que necesitan versatilidad auténtica.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"125 cc",
potencia:"12 HP",
peso:"118 kg",
transmision:"5 velocidades",
tanque:"11 litros",
frenos:"Tambor",
suspension:"Largo recorrido"
}

},


{
id:24,
nombre:"Yamaha YZF-R3 250",
marca:"Yamaha",
precio:15000,
tipo:"deportiva",
imagen:"./assets/images/Yamaha YZF-R3 250c.jpg",

imagenes:[

"./assets/images/Yamaha YZF-R3 250c.jpg",

"./assets/images/Yamaha YZF-R3 250c-2.jpg",

"./assets/images/Yamaha YZF-R3 250c-3.jpg"

],

descripcion:"La Yamaha YZF-R3 250 es una motocicleta deportiva pura que trae tecnología de supersport a una cilindrada accesible. Su motor bicilíndrico paralelo de 321 cc y 42 HP desarrolla potencia emocionante con excelente control. Carenado completo aerodinámico reduce resistencia del viento a alta velocidad. Horquilla invertida proporciona precisión de dirección excepcional en curvas cerradas. Frenos de disco ABS garantizan frenadas seguras incluso en condiciones límite. Suspensión deportiva calibrada permite conducción en circuito sin sacrificar comodidad en carretera. Posición de conducción deportiva baja evoca el espíritu de supersport legendarias. Peso optimizado de 169 kg mantiene inercia bajo control. Transmisión de 6 velocidades permite aprovechar potencia en cada rango. LED lights modernas mejoran presencia visual en la vía. Perfecta para motociclistas que buscan auténtica experiencia deportiva con presupuesto inteligente.",

especificaciones:{
motor:"Bicilíndrico paralelo",
cilindrada:"321 cc",
potencia:"42 HP",
peso:"169 kg",
transmision:"6 velocidades",
tanque:"14 litros",
frenos:"ABS",
suspension:"Telescópica"
}

},


{
id:25,
nombre:"Suzuki Gixxer 150",
marca:"Suzuki",
precio:9000,
tipo:"deportiva",
imagen:"./assets/images/Suzuki Gixxer 150.jpg",

descripcion:"La Suzuki Gixxer 150 es una motocicleta deportiva urbana que combina economía con desempeño deportivo auténtico. Su motor monocilíndrico de 155 cc y 13.6 HP proporciona aceleración ágil en tráfico urbano. Diseño agresivo moderno refleja el linaje deportivo de la familia Gixxer. Frenos de disco delantero proporcionan potencia de frenada superior en comparación a competidores. Suspensión telescópica calibrada absorbe irregularidades del pavimento manteniendo estabilidad. Peso ligero de 141 kg facilita maniobras ágiles en espacios reducidos. Tanque de 12 litros permite viajes moderadamente largos sin recargas frecuentes. Transmisión de 5 velocidades optimiza el consumo de combustible ultra eficiente. Chasis robusto garantiza durabilidad en uso diario intenso. Ideal para motociclistas jóvenes y principiantes que buscan estilo deportivo sin romper el presupuesto.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"155 cc",
potencia:"13.6 HP",
peso:"141 kg",
transmision:"5 velocidades",
tanque:"12 litros",
frenos:"Disco",
suspension:"Telescópica"
}

},


{
id:26,
nombre:"Suzuki V-Strom 250",
marca:"Suzuki",
precio:24000,
tipo:"adventure",
imagen:"./assets/images/Suzuki V-Strom 250.jpg",

descripcion:"La Suzuki V-Strom 250 es una motocicleta adventure compacta versátil para viajes variados y exploración moderada. Su motor bicilíndrico de 248 cc y 25 HP ofrece potencia equilibrada para ciudad y carretera. Tanque amplio de 17 litros proporciona autonomía buena para viajes de fin de semana en rutas remotas. Suspensión telescópica con buen recorrido maneja irregularidades de caminos rusticos. Sistema de frenos ABS aumenta seguridad en frenadas de emergencia en condiciones variadas. Posición de conducción erguida proporciona comodidad excepcional en viajes extendidos. Peso manejable de 188 kg permite control fácil tanto en carretera como en terrenos más complicados. Chasis robusto construido para durabilidad en condiciones exigentes. Diseño moderno con líneas prácticas refleja filosofía adventure versátil. Perfecta para viajeros que buscan equilibrio entre adventure pura y comodidad práctica.",

especificaciones:{
motor:"Bicilíndrico",
cilindrada:"248 cc",
potencia:"25 HP",
peso:"188 kg",
transmision:"6 velocidades",
tanque:"17 litros",
frenos:"ABS",
suspension:"Telescópica"
}

},


{
id:27,
nombre:"TVS Apache RTR 160",
marca:"TVS",
precio:8500,
tipo:"deportiva",
imagen:"./assets/images/TVS Apache RTR 160.jpg",

descripcion:"La TVS Apache RTR 160 es una motocicleta deportiva económica que ofrece excelente respuesta del motor y consumo ultra eficiente. Su motor monocilíndrico de 160 cc y 15 HP proporciona aceleración emocionante sin ser exigente. Diseño agresivo moderno con líneas deportivas diferencia esta moto de competidores directos. Frenos de disco delantero proporcionan potencia de frenada superior en maniobras de emergencia. Suspensión telescópica calibrada absorbe irregularidades del pavimento urbano. Peso ligero de 137 kg facilita maniobrabilidad en tráfico congestionado. Tanque de 12 litros permite viajes de distancia moderada sin recargas constantes. Transmisión de 5 velocidades optimiza consumo de combustible entre los mejores del mercado. Chasis robusto garantiza durabilidad en uso urbano intenso. Ideal para conductores presupuestarios que buscan desempeño deportivo auténtico y economía de operación.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"160 cc",
potencia:"15 HP",
peso:"137 kg",
transmision:"5 velocidades",
tanque:"12 litros",
frenos:"Disco",
suspension:"Telescópica"
}

},


{
id:28,
nombre:"Royal Enfield Himalayan",
marca:"Royal Enfield",
precio:27000,
tipo:"adventure",
imagen:"./assets/images/Royal Enfield Himalayan.jpg",

descripcion:"La Royal Enfield Himalayan es una motocicleta adventure clásica diseñada específicamente para viajes épicos en terrenos variados de montaña. Su motor monocilíndrico de 411 cc y 24 HP desarrolla potencia lineal perfecta para escaladas en terreno alto. Chasis robusto construido con acero tubular para durabilidad extrema en condiciones primitivas. Suspensión de largo recorrido absorbe golpes severos de caminos de montaña con facilidad. Sistema de frenos ABS proporciona seguridad en frenadas en altitud y superficies irregulares. Posición de conducción erguida proporciona comodidad excepcional en viajes de múltiples días. Tanque grande de 15 litros permite recorrer distancias largas en zonas remotas sin recargas. Neumáticos con tacos proporcionan tracción confiable en tierra, grava y nieve ligera. Diseño clásico retro trae presencia visual memorable en cualquier ruta. Protecciones laterales protegen motor y tanque contra impactos de off-roading. Perfecta para aventureros legendarios que buscan conquista pura de rutas de alta montaña.",

especificaciones:{
motor:"Monocilíndrico",
cilindrada:"411 cc",
potencia:"24 HP",
peso:"199 kg",
transmision:"5 velocidades",
tanque:"15 litros",
frenos:"ABS",
suspension:"Largo recorrido"
}

}

];