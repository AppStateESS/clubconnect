
<h1>Club Registration <small>{TERM}</small></h1>
<p class="lead">Please fill out this form in its entirety to register an organization for {TERM}.</p>
{START_FORM}
<ul class="nav nav-tabs">
  <li class="active"><a href="#history" data-toggle="tab">History</a></li>
  <li class="disabled"><a href="#basics" data-toggle="tab">Basics</a></li>
  <li class="disabled"><a href="#profile" data-toggle="tab">Profile</a></li>
  <li class="disabled"><a href="#people" data-toggle="tab">People</a></li>
  <li class="disabled"><a href="#elections" data-toggle="tab">Elections</a></li>
  <li class="disabled"><a href="#review" data-toggle="tab">Review</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane active" id="history">
    <p>Are you registering for a new organization or are you re-registering a previously existing club?</p>
    {HAS_HISTORY_2} Registering for a New Organization<br />
    {HAS_HISTORY_1} Re-Registering an Existing Organization
    <div id="has-history">{PARENT}</div>
    <a href="#basics" class="btn btn-large btn-primary pull-right" data-toggle="tab">Next Page <i class="icon-arrow-right"></i></a>
  </div>
  <div class="tab-pane" id="basics">
    <div id="history-type"><p>Your new club registration will be categorized as: <strong id="history-type-value"></strong></p></div>
    <p>Please enter the name of your club or organization.  If you would like to change the name, now is the time.<br />{NAME}</p>
    <p>Enter your club's ASU PO Box Number or Campus Departmental Address.  You cannot use your personal ASU PO Box.<br />{ADDRESS}</p>
    <p>If your organization has a bank account, we need to know the name of your bank.  We do not need your account number.<br />{BANK}</p>
    <p>EIN (Employee Identification Number).  You must obtain this number from the IRS to open the bank account for your organization.<br />{EIN}</p>
    <a href="#history" class="btn btn-large btn-default pull-left" data-toggle="tab"><i class="icon-arrow-left"></i> Go Back</a>
    <a href="#profile" class="btn btn-large btn-primary pull-right" data-toggle="tab">Next Page <i class="icon-arrow-right"></i></a>
  </div>
  <div class="tab-pane" id="profile">
    <p>Please select your role in the club this year.</p>
    {USER_TYPE_1} President<br />
    {USER_TYPE_2} Advisor<br />
    {USER_TYPE_3} Other
    <div>
      <a href="#basics" class="btn btn-large btn-default pull-left" data-toggle="tab"><i class="icon-arrow-left"></i> Go Back</a>
      <a href="#people" class="btn btn-large btn-primary pull-right" data-toggle="tab">Next Page <i class="icon-arrow-right"></i></a>
    </div>
  </div>
  <div class="tab-pane" id="people">
    <div id="search-pres">
        <h3>Club President</h3>
        <p>Please select a club president by searching below.  If you are unable to find who you are looking for, please contact CSIL.</p>
        {PRES_SEARCH}
        <div id="pres-search"></div>
    </div>
    <div id="search-advisor">
        <h3>Club Advisor</h3>
        <p>Please select a club advisor by searching below.  If you are unable to find your advisor, please <a href="#" id="advisor-specify">click here to specify your club advisor manually</a>.</p>
        {ADVISOR_SEARCH}
        <div id="advisor-search"></div>
    </div>
    <div id="specify-advisor">
        <h3>Specify Advisor</h3>
        <p>Full Name<br />{REQ_ADVISOR_NAME}</p>
        <p>Department/Office<br />{REQ_ADVISOR_DEPT}</p>
        <p>Building<br />{REQ_ADVISOR_BLDG}</p>
        <p>Phone Number<br />{REQ_ADVISOR_PHONE}</p>
        <p>Email Address<br />{REQ_ADVISOR_EMAIL}</p>
        <p>Or, <a href="#" id="advisor-search">click here to search SDR for your club advisor</a>.</p>
    </div>
    <div id="go-back-4">
        <p>Please select your role in in the previous step before choosing administrators.</p>
    </div>
    <a href="#profile" class="btn btn-large btn-default pull-left" data-toggle="tab"><i class="icon-arrow-left"></i> Go Back</a>
    <a href="#elections" class="btn btn-large btn-primary pull-right" data-toggle="tab">Next Page <i class="icon-arrow-right"></i></a>
  </div>
  <div class="tab-pane" id="elections">
    <p>In what month(s) do your elections take place?</p>
    <table style="width: 80%">
        <tr>
            <td>{ELECTION_MONTHS_1}  January</td>
            <td>{ELECTION_MONTHS_5}  May</td>
            <td>{ELECTION_MONTHS_9}  September</td>
        </tr>
        <tr>
            <td>{ELECTION_MONTHS_2}  February</td>
            <td>{ELECTION_MONTHS_6}  June</td>
            <td>{ELECTION_MONTHS_10} October</td>
        </tr>
        <tr>
            <td>{ELECTION_MONTHS_3}  March</td>
            <td>{ELECTION_MONTHS_7}  July</td>
            <td>{ELECTION_MONTHS_11} November</td>
        </tr>
        <tr>
            <td>{ELECTION_MONTHS_4}  April</td>
            <td>{ELECTION_MONTHS_8}  August</td>
            <td>{ELECTION_MONTHS_12} December</td>
        </tr>
    </table>
    <a href="#people" class="btn btn-large btn-default pull-left" data-toggle="tab"><i class="icon-arrow-left"></i> Go Back</a>
    <a href="#review" class="btn btn-large btn-primary pull-right" data-toggle="tab">Next Page <i class="icon-arrow-right"></i></a>
  </div>
  <div class="tab-pane" id="review">
    <div class="review"></div>
    <div class="form-section">
        <p style="text-align: right; font-size: 2em;">{SUBMIT}</p>
    </div>
    <a href="#elections" class="btn btn-large btn-default pull-left" data-toggle="tab"><i class="icon-arrow-left"></i> Go Back</a>
  </div>
</div>
{END_FORM}
