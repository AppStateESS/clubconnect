<h1>{FULLNAME} - {TERM}</h1>
<p>You are requesting to join <strong>{FULLNAME}</strong>.</p>
<!-- BEGIN AGREEMENTS -->
{CONTENT}
<!-- END AGREEMENTS -->
<form class="form-inline" action="{REQUEST}" method="POST">
  <input type="submit" class="btn btn-large btn-success" name="request" value="Request">
  <a class="btn btn-large btn-danger" href="{CANCEL}">Cancel</a>
</form>
