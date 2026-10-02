<div class="fs-admin__block">
    <h4>{tr:perform_these_actions_on_all_users}</h4>
    <button type="button" class="fs-button" data-action="all-delete-api-secret">{tr:api_secret_delete}</button>
</div>

<?php if( Config::get('using_local_saml_dbauth')) : ?>
<div class="fs-admin__block createuser" id="createuser">
    <h4>{tr:create_user}</h4>
    <p>{tr:create_user_details}</p>
    <div class="fs-admin__fields">
        <div class="fs-input-group">
            <label for="createusername" class="mandatory">{tr:email_address}</label>
            <input type="text" id="createusername" name="createusername" data-action="create-user-name" />
        </div>
        <div class="fs-input-group">
            <label for="createuserpassword" class="mandatory">{tr:password}</label>
            <input type="text" id="createuserpassword" name="createuserpassword" />
        </div>
        <button type="button" class="fs-button" name="create-user" data-action="create-user">{tr:create_user}</button>
    </div>
</div>
<?php endif;  ?>

<div class="fs-admin__block search">
    <h4>{tr:find_users_who_might_be_abusing_the_system}</h4>
    <ul class="fs-admin__abuse">
        <?php
        $abuse_searches = array(
            'ab_hit_create_total_limit' => 'hit_guest_creation_total_limit',
            'ab_hit_create_rate_limit' => 'hit_guest_creation_rate_limit',
            'ab_hit_remind_rate_limit' => 'hit_guest_remind_rate_limit',
            'ab_guests_no_file' => 'most_user_deleted_guests_that_did_not_send_a_single_file',
            'ab_guests_del' => 'most_guest_deletion',
            'ab_decryptfailed' => 'most_failed_decryptions',
        );
        $abuse_periods = array(
            'oneday' => 86400,
            'oneweek' => 604800,
            'twoeightdays' => 2419200,
        );
        foreach($abuse_searches as $class => $label) {
        ?>
            <li class="fs-admin__abuse-item">
                <span class="fs-admin__abuse-label"><?php echo Lang::tr($label) ?></span>
                <span class="fs-admin__abuse-actions">
                    <?php foreach($abuse_periods as $period => $since) { ?>
                        <button type="button" class="fs-button fs-button--inverted <?php echo $class ?>" data-since="<?php echo $since ?>"><?php echo Lang::tr($period) ?></button>
                    <?php } ?>
                </span>
            </li>
        <?php } ?>
    </ul>
</div>

<div class="fs-admin__block">
    <h4>{tr:search_user}</h4>
    <div class="fs-admin__fields search">
        <div class="fs-input-group">
            <label for="admin_user_match">{tr:user_id}</label>
            <input type="text" id="admin_user_match" name="match" />
        </div>
        <button type="button" class="fs-button" name="go">
            <i class="fi fi-search"></i>
            <span>{tr:search}</span>
        </button>
    </div>

    <div class="fs-admin__table">
        <table class="fs-table fs-table--striped fs-table--text-middle results">
            <tr>
                <th>ID</th>
                <th>{tr:user_id}</th>
                <th>{tr:last_activity}</th>
                <th>{tr:event_count}</th>
                <th>{tr:ip}</th>
                <th>&nbsp;</th>
            </tr>

            <tr class="searching">
                <td colspan="6">{tr:searching}</td>
            </tr>
            <tr class="no_results">
                <td colspan="6">{tr:no_results}</td>
            </tr>

            <tr class="tpl">
                <td class="id"></td>
                <td class="saml_id"></td>
                <td class="last_activity"></td>
                <td class="event_count"></td>
                <td class="ip"></td>

                <td class="fs-admin__actions">
                    <?php if( Config::get('admin_can_view_user_transfers_page')) : ?>
                        <button type="button" class="fs-button fs-button--inverted" data-action="show-transfers">{tr:show_transfers}</button>
                    <?php endif; ?>
                    <button type="button" class="fs-button fs-button--inverted" data-action="show-client-logs">{tr:show_client_logs}</button>
                    <button type="button" class="fs-button fs-button--inverted" data-action="delete-api-secret">{tr:api_secret_delete}</button>
                    <?php if( Config::get('using_local_saml_dbauth')) : ?>
                        <button type="button" class="fs-button fs-button--inverted" data-action="set-local-authdb-password">{tr:change_password}</button>
                    <?php endif; ?>
                    <button type="button" class="fs-button fs-button--inverted" data-action="set-default-guest-expires">{tr:set_default_guest_expire_days}</button>
                </td>
            </tr>
        </table>
    </div>

    <div class="fs-admin__table">
        <table class="fs-table fs-table--striped client-logs">
            <tr>
                <th>{tr:date}</th>
                <th>{tr:message}</th>
            </tr>

            <tr class="searching">
                <td colspan="2">{tr:searching}</td>
            </tr>
            <tr class="no_results">
                <td colspan="2">{tr:no_results}</td>
            </tr>

            <tr class="tpl">
                <td class="date"></td>
                <td class="message"></td>
            </tr>
        </table>
    </div>
</div>

<script type="text/javascript" src="{path:js/admin_users.js}"></script>
