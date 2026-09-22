@extends('legal.layout')

@section('title', 'Condiciones del Servicio')
@section('heading', 'Condiciones del Servicio')

@section('content')
<section>
    <h2 class="text-lg font-semibold text-slate-900">1. Objeto</h2>
    <p>Estas Condiciones regulan el acceso y uso de PodoSoft, una plataforma SaaS para la gestión integral de clínicas de podología (agenda, pacientes, historias clínicas, facturación, inventario, comunicaciones por WhatsApp e integraciones con servicios de terceros como Google Calendar). Al registrarse y usar la Plataforma, la clínica acepta estas Condiciones.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">2. La cuenta y sus usuarios</h2>
    <ul class="list-disc pl-5 space-y-1">
        <li>Cada clínica (tenant) es responsable de los usuarios que registra y de mantener la confidencialidad de sus credenciales.</li>
        <li>La clínica garantiza que la información que ingresa es veraz y que cuenta con las autorizaciones necesarias para tratar los datos de sus pacientes.</li>
        <li>PodoSoft puede suspender cuentas que incumplan estas Condiciones o la legislación aplicable.</li>
    </ul>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">3. Uso permitido</h2>
    <p>La Plataforma debe utilizarse conforme a la ley. Queda prohibido: intentar acceder a datos de otras clínicas, interferir con la seguridad o disponibilidad del servicio, enviar comunicaciones masivas no consentidas a través de las integraciones (WhatsApp, Google) o usar la Plataforma para fines ilícitos.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">4. Integraciones con terceros</h2>
    <ul class="list-disc pl-5 space-y-1">
        <li><strong>Google Calendar:</strong> la conexión es voluntaria y la realiza cada clínica autorizando su propia cuenta de Google mediante OAuth 2.0. La clínica puede revocar el acceso en cualquier momento desde la Plataforma o desde su cuenta de Google. Las citas eliminadas o modificadas se gestionan según la configuración de sincronización vigente.</li>
        <li><strong>WhatsApp:</strong> las comunicaciones se envían a través del número conectado por la clínica. La clínica es responsable del contenido de los mensajes y de contar con el consentimiento de sus destinatarios.</li>
        <li>PodoSoft no se responsabiliza de interrupciones, cambios o suspensiones de los servicios de terceros.</li>
    </ul>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">5. Responsabilidad de los datos clínicos</h2>
    <p>La clínica actúa como responsable del tratamiento de los datos de sus pacientes y PodoSoft como encargado del tratamiento. La clínica es la única responsable de las decisiones clínicas; la Plataforma es una herramienta de gestión y no reemplaza el criterio profesional.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">6. Disponibilidad del servicio</h2>
    <p>Procuramos mantener la Plataforma disponible de forma continua, pero pueden existir interrupciones por mantenimiento, actualizaciones o causas fuera de nuestro control. Notificaremos los mantenimientos programados cuando sea posible.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">7. Planes y pagos</h2>
    <p>El acceso a la Plataforma se sujeta al plan contratado. Los términos económicos, periodos de prueba y renovaciones se rigen por el acuerdo comercial suscrito con cada clínica.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">8. Limitación de responsabilidad</h2>
    <p>En la medida permitida por la ley, PodoSoft no será responsable por daños indirectos, lucro cesante o pérdida de datos derivados de un uso indebido de la Plataforma o de fallos de servicios de terceros integrados.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">9. Terminación</h2>
    <p>La clínica puede cancelar su cuenta en cualquier momento. A la terminación, podrá exportar su información y posteriormente los datos se eliminarán conforme a nuestra Política de Privacidad.</p>
</section>

<section>
    <h2 class="text-lg font-semibold text-slate-900">10. Modificaciones y legislación</h2>
    <p>Podemos actualizar estas Condiciones, notificándolo a través de la Plataforma. El uso continuado implica la aceptación de la versión vigente. Estas Condiciones se rigen por la legislación del país de operación de PodoSoft.</p>
</section>
@endsection
