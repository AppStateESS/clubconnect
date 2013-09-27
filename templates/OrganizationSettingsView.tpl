<div class="org-settings">
{START_FORM}
<div class="form-section">
<h3>Permanent Organization Settings</h3>
<p>The following settings affect the organization as a whole, regardless of term.</p>
<p>{DISABLED} {DISABLED_LABEL} ({DISABLED_REASON_LABEL}: {DISABLED_REASON})</p>
<p>{AGREEMENT_LABEL}</p>
<p>{AGREEMENT}</p>
</div>

<div class="form-section">
<h3>Settings for {TERM}</h3>
<p>The following settings only affect the currently selected term, unless otherwise specified.</p>
<p>{REGISTERED} {REGISTERED_LABEL}
<!-- BEGIN IF_REGISTERED -->
<div id="if-org-registered">
    <dl>
        <dt>{NAME_LABEL}</dt><dd>{NAME}</dd>
        <dt>{TYPE_LABEL}</dt><dd>{TYPE}</dd>
        <dt>{RETROACTIVE} {RETROACTIVE_LABEL}</dt>
        <dt>{ADDRESS_LABEL}</dt><dd>{ADDRESS}</dd>
        <dt>{BANK_LABEL}</dt><dd>{BANK}</dd>
        <dt>{EIN_LABEL}</dt><dd>{EIN}</dd>
    </dl>
</div>
<!-- END IF_REGISTERED -->
</div>
<div class="form-section">
<h3>Submit Changes</h3>
{SUBMIT}
</div>
{END_FORM}
</div>
