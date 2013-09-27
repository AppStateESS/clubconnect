<h1>{FULLNAME} - {TERM}</h1>
<p>You are agreeing to accept the position of <strong>{ROLE}</strong> in the <strong>{FULLNAME}</strong> organization.</p>
<!-- BEGIN AGREEMENTS -->
{CONTENT}
<!-- END AGREEMENTS -->
<form class="form-inline" action="{FORMURI}" method="POST">
  <input type="submit" class="btn btn-large btn-success" name="accept" value="Accept">
  <input type="submit" class="btn btn-large btn-danger" name="reject" value="Reject">
</form>
