<h1>{FULLNAME} - {TERM}</h1>
<p>You are accepting a request to join <strong>{FULLNAME}</strong>.</p>
<!-- BEGIN AGREEMENTS -->
{CONTENT}
<!-- END AGREEMENTS -->
<form class="form-inline" action="{ACCEPT}" method="POST">
  <input type="submit" class="btn btn-large btn-success" name="accept" value="Accept">
  <a class="btn btn-large btn-danger" href="{CANCEL}">Cancel</a>
</form>
