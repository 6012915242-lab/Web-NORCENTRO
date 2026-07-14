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

descripcion:"Motocicleta compacta y económica ideal para desplazamientos urbanos.",

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

descripcion:"Motocicleta KTM preparada para aventura con tecnología moderna.",

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

descripcion:"Naked deportiva ligera con gran maniobrabilidad y estilo agresivo.",

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

descripcion:"Moto deportiva KTM equilibrada entre potencia, control y comodidad.",

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

descripcion:"Naked deportiva Yamaha con diseño agresivo, ligera y eficiente para ciudad.",

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

descripcion:"Motocicleta urbana deportiva con excelente comodidad y rendimiento.",

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

descripcion:"Naked deportiva Yamaha con motor bicilíndrico y alto desempeño.",

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

descripcion:"Scooter urbano cómodo y económico para movilidad diaria.",

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

descripcion:"Motocicleta deportiva Yamaha de alto rendimiento inspirada en competición.",

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

descripcion:"Adventure preparada para viajes largos y terrenos exigentes.",

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

descripcion:"Moto doble propósito resistente para ciudad y caminos rurales.",

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

descripcion:"Motocicleta deportiva Yamaha con excelente equilibrio entre potencia y control.",

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

descripcion:"Moto urbana deportiva económica y confiable.",

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

descripcion:"Adventure compacta para viajes y uso mixto.",

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

descripcion:"Moto deportiva económica con excelente respuesta y consumo.",

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

descripcion:"Moto adventure clásica diseñada para viajes largos y terrenos variados.",

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