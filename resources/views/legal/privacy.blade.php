@extends('legal.layout')

@section('title', 'Política de Privacidad')
@section('heading', 'Política de Privacidad')

@section('content')
<section>
    <h2 class="text-lg font-semibold text-slate-900">1. Introducción</h2>
    <p>PodoSoft ("nosotros", "la Plataforma") es un software como servicio (SaaS) para la gestión de clínicas de podología. Esta Política de Privacidad describe cómo recopilamos, usamos, almacenamos y protegemos la información de nuestros usuarios (las clínicas) y de los datos que ellas gestionan a través de la Plataforma, incluidos datos de sus pacientes.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">2. Información que recopilamos</h2>
    <ul class="list-disc pl-5 space-y-1">
        <li><strong>Datos de cuenta:</strong> nombre de la clínica, correo electrónico, datos de contacto y credenciales de acceso de los usuarios administrativos.</li>
        <li><strong>Datos operativos ingresados por la clínica:</strong> pacientes, citas, historias clínicas, facturación, inventario y pagos. La clínica es la responsable de estos datos; PodoSoft actúa como encargado del tratamiento.</li>
        <li><strong>Datos de integraciones:</strong> cuando la clínica conecta servicios de terceros (por ejemplo, Google Calendar o WhatsApp a través de Evolution API), procesamos los datos estrictamente necesarios para prestar esa funcionalidad.</li>
        <li><strong>Datos técnicos:</strong> registros de acceso, dirección IP y logs de uso con fines de seguridad y operación.</li>
    </ul>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">3. Integración con Google Calendar</h2>
    <p>Si la clínica decide conectar su cuenta de Google mediante OAuth 2.0:</p>
    <ul class="list-disc pl-5 space-y-1">
        <li>Solicitamos únicamente los permisos mínimos necesarios para crear, actualizar y eliminar eventos del calendario seleccionado y conocer el correo de la cuenta autorizada.</li>
        <li>Los tokens de acceso se almacenan <strong>cifrados</strong> y asociados exclusivamente a la clínica que autorizó la conexión. Ninguna clínica puede acceder a la cuenta o calendario de otra.</li>
        <li>Solo sincronizamos los datos de las citas necesarios para crear el evento (fecha, hora, paciente y servicio). No leemos correos, contactos ni otros datos de la cuenta de Google.</li>
        <li>La clínica puede desconectar la integración en cualquier momento desde la configuración de la Plataforma; al hacerlo revocamos la autorización y eliminamos los tokens almacenados.</li>
    </ul>
    <p>El uso de la información recibida de las API de Google se adhiere a la <a class="text-indigo-600 hover:underline" href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener">Política de Datos de Usuario de los Servicios API de Google</a>, incluidos los requisitos de uso limitado.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">4. Uso de la información</h2>
    <p>Utilizamos la información exclusivamente para: prestar y mejorar el servicio, gestionar citas y comunicaciones (incluidas notificaciones por WhatsApp conforme a la configuración de cada clínica), garantizar la seguridad de la Plataforma y cumplir obligaciones legales. No vendemos ni compartimos datos con terceros con fines publicitarios.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">5. Almacenamiento y seguridad</h2>
    <p>Los datos se almacenan en servidores seguros con acceso restringido, cifrado de credenciales y datos sensibles, y controles de aislamiento entre clínicas (multi-tenant). Aplicamos medidas técnicas y organizativas razonables para proteger la información contra accesos no autorizados, pérdida o alteración.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">6. Derechos de los titulares</h2>
    <p>Las clínicas pueden solicitar el acceso, rectificación, exportación o eliminación de sus datos contactándonos. Los pacientes deben dirigir sus solicitudes a la clínica correspondiente, que es la responsable de sus datos.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">7. Conservación y eliminación</h2>
    <p>Conservamos los datos mientras la cuenta de la clínica esté activa. Ante la cancelación del servicio, los datos podrán exportarse y posteriormente se eliminarán conforme a nuestras políticas de retención.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">8. Cambios a esta política</h2>
    <p>Podemos actualizar esta Política de Privacidad. Notificaremos los cambios relevantes a través de la Plataforma o por correo electrónico.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">9. Contacto</h2>
    <p>Para consultas sobre privacidad y protección de datos, escríbenos al correo de soporte publicado en nuestro sitio web.</p>
</section>
@endsection
