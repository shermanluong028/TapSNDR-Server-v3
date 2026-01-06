@php
    $allowedFields = \App\Models\User::getAllowedFields($currentUser->role, 'r');
    $allowedFields[] = 'role';

    $user = array_intersect_key($currentUser->toArray(), array_flip($allowedFields));
@endphp

<script>
    window.TapSNDRCurrentUser = {{ Js::from($user) }};
</script>
