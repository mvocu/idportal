<html>
<head>
</head>
<body>
<form method="POST" action="{{ route('password.send') }}">
{{ csrf_field() }}

<input id="uid" type="hidden" name="uid" value="{{ $phone }}" />
<input id="preferred_sms" type="hidden" name="preferred" value="sms" />
</form>
<script>
document.forms[0].submit();
</script>
</body>
</html>
  