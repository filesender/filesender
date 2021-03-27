
<h2>{tr:perform_these_actions_on_all_users}</h2>
    <table class="">
        <tr>
            <td>
                <input type="button" data-action="all-delete-api-secret" value="{tr:api_secret_delete}" />
            </td>
        </tr>
    </table>
<br/>

<?php if( Config::get('using_local_saml_dbauth')) : ?>
<h2>{tr:create_user}</h2>
<p>{tr:create_user_details}</p>
<table id="createuser" name="createuser" class="createuser">
    <colgroup>
        <col width="15%"><col width="85%">
    </colgroup>
        <tr>
        <td>
            <label for="createusername" class="mandatory">{tr:email_address}</label>
        </td><td>
            <input type="text" name="createusername" data-action="create-user-name" />
        </td>
    </tr><tr>
        <td>
            <label for="createuserpassword" class="mandatory">{tr:password}</label>
        </td><td>
            <input type="text" name="createuserpassword" />
        </td>
    </tr>
    <tr><td colspan="2" >
        <input type="button" name="create-user" data-action="create-user" value="{tr:create_user}" />
    </td></tr>
</table>
<?php endif;  ?>
    
<br/>




<h2>{tr:find_users_who_might_be_abusing_the_system}</h2>
<fieldset class="search">
<table>
    <tr><td>{tr:hit_guest_creation_total_limit}</td><td>
        <input type="button" class="ab_hit_create_total_limit" value="{tr:oneday}"       data-since="86400" />
        <input type="button" class="ab_hit_create_total_limit" value="{tr:oneweek}"      data-since="604800" />
        <input type="button" class="ab_hit_create_total_limit" value="{tr:twoeightdays}" data-since="2419200" />
    </td></tr>
    <tr><td>{tr:hit_guest_creation_rate_limit}</td><td>
        <input type="button" class="ab_hit_create_rate_limit" value="{tr:oneday}"       data-since="86400" />
        <input type="button" class="ab_hit_create_rate_limit" value="{tr:oneweek}"      data-since="604800" />
        <input type="button" class="ab_hit_create_rate_limit" value="{tr:twoeightdays}" data-since="2419200" />
    </td></tr>
    <tr><td>{tr:hit_guest_remind_rate_limit}</td><td>
        <input type="button" class="ab_hit_remind_rate_limit" value="{tr:oneday}"       data-since="86400" />
        <input type="button" class="ab_hit_remind_rate_limit" value="{tr:oneweek}"      data-since="604800" />
        <input type="button" class="ab_hit_remind_rate_limit" value="{tr:twoeightdays}" data-since="2419200" />
    </td></tr>
    <tr><td>{tr:most_user_deleted_guests_that_did_not_send_a_single_file}</td><td>
        <input type="button" class="ab_guests_no_file" value="{tr:oneday}"        data-since="86400" />
        <input type="button" class="ab_guests_no_file" value="{tr:oneweek}"       data-since="604800" />
        <input type="button" class="ab_guests_no_file" value="{tr:twoeightdays}"  data-since="2419200" />
    </td></tr>
    <tr><td>{tr:most_guest_deletion}</td><td>
        <input type="button" class="ab_guests_del" value="{tr:oneday}"          data-since="86400" />
        <input type="button" class="ab_guests_del" value="{tr:oneweek}"         data-since="604800" />
        <input type="button" class="ab_guests_del" value="{tr:twoeightdays}"    data-since="2419200" />
    </td></tr>
</table>
</fieldset>

<h2>{tr:search_user}</h2>
<fieldset class="search">
    
    <input type="text" name="match" /> <input type="button" name="go" value="{tr:search}" />
</fieldset>


<table class="results">
    <tr>
        <th>ID</th>
        <th>{tr:user_id}</th>
        <th>{tr:last_activity}</th>
        <th>{tr:event_count}</th>
        <th>&nbsp;</th>
    </tr>
    
    <tr class="searching">
        <td colspan="4">{tr:searching}</td>
    </tr>
    <tr class="no_results">
        <td colspan="4">{tr:no_results}</td>
    </tr>
    
    <tr class="tpl">
        <td class="id"></td>
        <td class="saml_id"></td>
        <td class="last_activity"></td>
        <td class="event_count"></td>
        
        <td>
<?php if( Config::get('admin_can_view_user_transfers_page')) : ?>
            <input type="button" data-action="show-transfers" value="{tr:show_transfers}" />
<?php endif; ?>
            <input type="button" data-action="show-client-logs" value="{tr:show_client_logs}" />
            <input type="button" data-action="delete-api-secret" value="{tr:api_secret_delete}" />
<?php if( Config::get('using_local_saml_dbauth')) : ?>
            <input type="button" data-action="set-local-authdb-password" value="{tr:change_password}" />
<?php endif; ?>
            <input type="button" data-action="set-default-guest-expires" value="{tr:set_default_guest_expire_days}" />
        </td>
    </tr>
</table>

<table class="client-logs">
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

<script type="text/javascript" src="{path:js/admin_users.js}"></script>
