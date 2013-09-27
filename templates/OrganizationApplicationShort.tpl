<div id="OrganizationApplicationView">
    <div class="form-section">
        <h3>Previous Registration</h3>
        <!-- BEGIN PARENT -->
        <dl>
            <dt>{PARENT_LABEL}</dt><dd>{PARENT}</dd>
        </dl>
        <!-- END PARENT -->
        <!-- BEGIN NOPARENT -->
        <p>{NOPARENT}</p>
        <!-- END NOPARENT -->
        <!-- BEGIN PARENTERRORBLOCK -->
        <ul class="error">
            <!-- BEGIN PARENT_ERROR -->
            <li><a href="#oaf-1">{ERROR}</a></li>
            <!-- END PARENT_ERROR -->
        </ul>
        <!-- END PARENTERRORBLOCK -->
    </div>
    <div class="form-section">
        <h3>Categorization</h3>
        <!-- BEGIN CATEGORY -->
        <dl>
            <dt>{TYPE_LABEL}</dt><dd id="orgtype">{TYPE}</dd>
        </dl>
        <!-- END CATEGORY -->
        <!-- BEGIN CATEGORYERRORBLOCK -->
        <ul class="error">
            <!-- BEGIN CATEGORY_ERROR -->
            <li><a href="#oaf-1">{ERROR}</a></li>
            <!-- END CATEGORY_ERROR -->
        </ul>
        <!-- END CATEGORYERRORBLOCK -->
    </div>
    <div class="form-section">
        <h3>Basic Information</h3>
        <dl>
            <!-- BEGIN NAME -->
            <dt>{NAME_LABEL}:</dt><dd>{NAME}</dd>
            <!-- END NAME -->
            <!-- BEGIN ADDRESS -->
            <dt>{ADDRESS_LABEL}:</dt><dd>{ADDRESS}</dd>
            <!-- END ADDRESS -->
            <!-- BEGIN BANK -->
            <dt>{BANK_LABEL}:</dt><dd>{BANK}</dd>
            <!-- END BANK -->
            <!-- BEGIN EIN -->
            <dt>{EIN_LABEL}:</dt><dd>{EIN}</dd>
            <!-- END EIN -->
        </dl>
        <!-- BEGIN BASICERRORBLOCK -->
        <ul class="error">
            <!-- BEGIN BASIC_ERROR -->
            <li><a href="#oaf-2">{ERROR}</a></li>
            <!-- END BASIC_ERROR -->
        </ul>
        <!-- END BASICERRORBLOCK -->
    </div>
    <div class="form-section">
        <h3>User Classification</h3>
        <!-- BEGIN USER -->
        <dl>
            <dt>{USER_NAME_LABEL}:</dt>
            <dd>{USER_NAME} (<strong>{USER_TYPE_LABEL}:</strong> {USER_TYPE})</dd>
        </dl>
        <!-- END USER -->
        <!-- BEGIN USERERRORBLOCK -->
        <ul class="error">
            <!-- BEGIN USER_ERROR -->
            <li><a href="#oaf-3">{ERROR}</a></li>
            <!-- END USER_ERROR -->
        </ul>
        <!-- END USERERRORBLOCK -->
    </div>
    <div class="form-section">
        <h3>Administrators</h3>
        <dl>
            <!-- BEGIN PRESIDENT -->
            <dt>{PRESIDENT_LABEL}:</dt><dd>{PRESIDENT}</dd>
            <!-- END PRESIDENT -->
            <!-- BEGIN ADVISOR -->
            <dt>{ADVISOR_LABEL}:</dt>
            <!-- BEGIN EXISTING_ADVISOR -->
            <dd>{ADVISOR}</dd>
            <!-- END EXISTING_ADVISOR -->
            <!-- BEGIN NEW_ADVISOR -->
            <dd>
                <span id="orgapp-advisor-name">{NEW_ADVISOR_NAME}</span><br />
                <span id="orgapp-advisor-dept">{NEW_ADVISOR_DEPT}</span><br />
                <span id="orgapp-advisor-bldg">{NEW_ADVISOR_BLDG}</span><br />
                <span id="orgapp-advisor-phone">{NEW_ADVISOR_PHONE}</span><br />
                <span id="orgapp-advisor-email">{NEW_ADVISOR_EMAIL}</span>
            </dd>
            <!-- END NEW_ADVISOR -->
            <!-- END ADVISOR -->
        </dl>
        <!-- BEGIN ADMINERRORBLOCK -->
        <ul class="error">
            <!-- BEGIN ADMIN_ERROR -->
            <li><a href="#oaf-4">{ERROR}</a></li>
            <!-- END ADMIN_ERROR -->
        </ul>
        <!-- END ADMINERRORBLOCK -->
    </div>
    <div class="form-section">
        <h3>Website</h3>
        <dl>
            <!-- BEGIN EXISTING_WEBSITE -->
            <dt>{EXISTING_WEBSITE_LABEL}:</dt><dd>{EXISTING_WEBSITE}</dd>
            <!-- END EXISTING_WEBSITE -->
            <!-- BEGIN DESIRED_WEBSITE -->
            <dt>{DESIRED_WEBSITE_LABEL}:</dt><dd>{DESIRED_WEBSITE}</dd>
            <!-- END DESIRED_WEBSITE -->
            <!-- BEGIN NO_WEBSITE -->
            <dt>{NO_WEBSITE_LABEL}:</dt><dd>{NO_WEBSITE}</dd>
            <!-- END NO_WEBSITE -->
        </dl>
        <!-- BEGIN WEBSITEERRORBLOCK -->
        <ul class="error">
            <!-- BEGIN WEBSITE_ERROR -->
            <li><a href="#oaf-5">{ERROR}</a></li>
            <!-- END WEBSITE_ERROR -->
        </ul>
        <!-- END WEBSITEERRORBLOCK -->
    </div>
    <div class="form-section">
        <h3>Election Months</h3>
        <dl>
            <!-- BEGIN ELECTION_MONTHS -->
            <dt>{ELECTION_MONTHS_LABEL}:</dt><dd>{ELECTION_MONTHS}</dd>
            <!-- END ELECTION_MONTHS -->
        </dl>
        <!-- BEGIN ELECTIONERRORBLOCK -->
        <ul class="error">
            <!-- BEGIN ELECTION_ERROR -->
            <li><a href="#oaf-6">{ERROR}</a></li>
            <!-- END ELECTION_ERROR -->
        </ul>
        <!-- END ELECTIONERRORBLOCK -->
    </div>

    <!-- BEGIN ADMINISTRATIVE -->
    <div class="form-section">
        <h3>Next Actions</h3>
        <!-- BEGIN FORM -->
        <div class="mini-form-section">
            {START_FORM}
            {SUBMIT}
            {END_FORM}
        </div>
        <!-- END FORM -->
        <div style="clear: both;"></div>
    </div>
    <!-- END ADMINISTRATIVE -->
</div>
