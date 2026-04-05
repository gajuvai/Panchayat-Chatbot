@component('mail::message')
# Complaint Status Updated

Hello **{{ $complaint->user->name }}**,

Your complaint has been reviewed and its status has been updated.

---

**Complaint Number:** {{ $complaint->complaint_number }}
**Title:** {{ $complaint->title }}
**New Status:** {{ $complaint->status->label() }}

---

**Message from Admin:**

{{ $adminMessage }}

@component('mail::button', ['url' => $viewUrl, 'color' => 'primary'])
View Complaint
@endcomponent

If you have any questions, please reply to this email or contact the admin office.

Thanks,
{{ config('app.name') }}
@endcomponent
