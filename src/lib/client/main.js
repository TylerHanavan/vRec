
function get_table_body_rows(table_element) {
    return table_element.children('tbody').children('tr.table-body-row');
}

function do_filter_table(event) {
    let search = event.data.search;
    let table_element = event.data.table_element;
    let rows = get_table_body_rows(table_element);
    if(rows == null) {
        //console.log("No rows found in filter table");
        return null;
    }
    let searchText = search.val().toLowerCase();
    rows.each(function(index) {
        const startTime = Date.now();
        let rowText = '';
        $(this).find('td').not('[filter-table-exclude-search="true"]').each(function() {
            rowText += $(this).text().toLowerCase() + ' ';
        });
        if(!rowText.includes(searchText)) {
            $(this).hide();
        } else {
            $(this).show();
        }

        const endTime = Date.now();
        console.log('do_filter_table run time:', endTime - startTime, 'ms');
    });
}

function register_tables() {
    $(".filter-table").each(function(index) {
        const startTime = Date.now();
        let table = $(this);
        let search = table.children(".form-inline").children(".table-search");
        let table_element = table.children(".styled-table");
        if(search != null && table_element != null) {
            search.on("input", {
                search: search,
                table_element: table_element
            }, do_filter_table);
        }

        const endTime = Date.now();
        console.log('register_tables run time:', endTime - startTime, 'ms');
    });
}

function clear_modal_form(form) {
    form.children('div').each(function(index) {
        $(this).children('input').each(function(index) {
            if($(this).attr('type') == 'submit' || $(this).attr('name') == 'table')
                return;
            $(this).val('');
        });
    });
}

function hide_element_with_delay(element, delay) {
    setTimeout(function() {
        element.hide();
    }, delay);
}

function query_xhr(url, method, data, callback) {
    if(callback == null || callback == undefined) {
        //console.log('query_xhr: callback is null or undefined : (' + callback + ')');
    }
    $.ajax({
        url: url,
        method: method,
        data: data,
        success: function(data) {
            callback(data);
        },
        error: function(data) {
            console.log(data);
        }
    });
}

function xhr(url, method, data, success, error) {
    $.ajax({
        url: url,
        method: method,
        data: data,
        success: success,
        error: error
    });
}

function create_login_modal() {
    let modal = $("<div></div>");
    modal.addClass('modal');
    modal.addClass('fade');
    modal.attr('id', 'login-modal');
    modal.attr('tabindex', '-1');
}

class FilterTable {
    constructor(table_name, data, columns) {
        this.table_name = table_name;
        this.data = data;
        this.columns = columns;
        this.modals = [];
        this.html_element = null;
        this.table_element = null;
    }
    getTableElement() {
        return this.table_element;
    }
    getTableName() {
        return this.table_name;
    }
    getData() {
        return this.data;
    }
    getColumns() {
        return this.columns;
    }
    getModals() {
        return this.getModals;
    }
    addModal(modal) {
        this.modals.push(modal);
    }
    getModal(name) {
        return null;
    }
    setHtmlElement(element) {
        this.html_element = element;
    }
    getHtmlElement() {
        return this.html_element;
    }
    populateFilterTable() {
    }
}

class RecordsFilterTable extends FilterTable {
    constructor(table_name, data, columns) {
        super(table_name, data, columns);
        this.table_name = table_name;
        this.data = data;
        this.columns = columns;
        this.modals = [];
        this.html_element = null;
        this.table_element = null;
    }
    getModal(name) {
        for(let i = 0; i < this.modals.length; i++) {
            if(this.modals[i].getModalId() == name)
                return this.modals[i];
        }
        return null;
    }
    updateTable(elements, record_name, xhr_response, filter_search_criteria) {
        const startTime = Date.now();
        let tbody = $("<tbody></tbody>");

        //console.log('RecordsFilterTable::updateTable: xhr_response', xhr_response);
    
        for(let i = 0; i < xhr_response['records'].length; i++) {
            let record = xhr_response['records'][i];
            let tr = $("<tr></tr>");
            tr.addClass('table-body-row');
            tr.hide();
            if(filter_search_criteria == null || filter_search_criteria == '' || filter_search_criteria == undefined) {
                tr.show();
            }
            tbody.append(tr);
            tr.attr('xhr-record-name', record_name);
            let row_id = -1;
            for(let j = 0; j < xhr_response['record_definition']['record_fields'].length; j++) {
                let field = xhr_response['record_definition']['record_fields'][j]['field_name'];
                if(field == 'table')
                    continue;
                if(field == 'id') {
                    tr.attr('xhr-record-id', record[field]['value']);
                    row_id = record[field]['value'];
                }
                let cell = $("<td></td>");
                cell.attr("xhr-record-type", xhr_response['record_definition']['record_fields'][j]['field_type']);
                cell.attr("xhr-field-name", field);
                tr.append(cell)
                let p = $("<p></p>");
                if(field != 'id')
                    p.attr('ondblclick', 'makeEditable(this)');
                p.text(record[field]['value']);
                cell.append(p);

                if(filter_search_criteria != null && filter_search_criteria != '' && filter_search_criteria != undefined) {
                    let val = record[field]['value'].toString();
                    if(val != null && val.toLowerCase().includes(filter_search_criteria.toLowerCase()))
                        tr.show();
                }
            }
            
            let actionsTd = $("<td></td>");
            actionsTd.attr('filter-table-exclude-search', 'true');
            tr.append(actionsTd);
            let actions = $("<div></div>");
            actionsTd.append(actions);
            let auditBtn = $("<button></button>");
            auditBtn.addClass('btn');
            auditBtn.addClass('btn-primary');
            auditBtn.attr('data-toggle', 'modal');
            auditBtn.attr('data-target', '#audit-modal');
            auditBtn.attr('record-audit-row-id', row_id);
            auditBtn.text('Audit');
            auditBtn.click(function(event) {
                let modal = $('#audit-modal');
                let record_id = $(this).attr('record-audit-row-id');
                let record_name = $(this).parent().parent().parent().attr('xhr-record-name');
                let modalBody = modal.find('.modal-body');
                let auditTable = $("<table></table>");
                //TODO: Add user who did this
                let headers = ['Time', 'URI', 'Old Value', 'New Value'];
                let thead = $("<thead></thead>");
                let tbody = $("<tbody></tbody>");
                auditTable.append(thead);
                auditTable.append(tbody);
                modalBody.text('');
                modalBody.append(auditTable);
                let tr = $("<tr></tr>");
                thead.append(tr);
                for(let i = 0; i < headers.length; i++) {
                    let th = $("<th></th>");
                    tr.append(th);
                    th.text(headers[i]);
                }
                //TODO: Don't re-render the table every time the audit button is clicked, reuse rows and append or hide
                let callbackFn = function(data) {
                    let auditData = JSON.parse(data);
                    if(auditData != null && auditData['audit_events'] != null && auditData['audit_events'].length > 0) {
                        console.log(auditData);
                        auditData = auditData['audit_events'];
                        for(let i = 0; i < auditData.length; i++) {
                            let auditRow = auditData[i];
                            let tr = $("<tr></tr>");
                            tbody.append(tr);
                            let td = $("<td></td>");
                            tr.append(td);
                            td.text(auditRow['event_time']);
                            td = $("<td></td>");
                            tr.append(td);
                            td.text(auditRow['cms_path']);
                            td = $("<td></td>");
                            tr.append(td);
                            td.text(auditRow['old_value']);
                            td = $("<td></td>");
                            tr.append(td);
                            td.text(auditRow['new_value']);
                        }
                    }
                };
                query_xhr("/xhr/audit", "POST", {"record_name": record_name, "row_id": record_id}, callbackFn);
            });
            actions.append(auditBtn);

            //TODO: Add other buttons (Edit, Delete, Copy)
        }

        const endTime = Date.now();
        console.log('updateTable run time:', endTime - startTime, 'ms');
        return tbody;
    }
    generateModal(fields, record_name) {
        const startTime = Date.now();
        let filter_table = this;
        let modal = new Modal('add-new-record-modal', 'add-new-record-modal');
        fields.push({field_name: 'table', hidden: true, field_value: record_name});
        modal.addForm('POST', '/new', fields);
        modal.addFormOnSubmit(function(event) {
            let form = $(this).parent().parent().children('.modal-body').children('form');
            let alert = $(this).parent().parent().children('.modal-body').children('.alert-success');
    
            let data = form.serialize();
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: data,
                success: function(data) {
                    clear_modal_form(form);
                    alert.show();
                    hide_element_with_delay(alert, 3000);
                    let callbackFn = function(data) {
                        let table_element = filter_table.getTableElement();
                        table_element.children('tbody').replaceWith(filter_table.updateTable(table_element.parent(), record_name, JSON.parse(data), filter_table.getHtmlElement().children('form').children('input').val()));
                    };
                    query_xhr("/xhr/record", "POST", {"record_name": record_name}, callbackFn);
                },
                error: function(data) {
                    console.log(data);
                }
            });
        });

        let auditModal = new Modal('audit-modal', 'Audit Data');
        auditModal.resizeLarge();

        let p = $("<p></p>");
        p.text('Audit Data');
        auditModal.addBodyElement(p);

        const endTime = Date.now();
        console.log('generateModal run time:', endTime - startTime, 'ms');
        return [modal, auditModal];
    }
    populateFilterTable() {
        const startTime = Date.now();
        let elements = this.getHtmlElement();
        let xhr_response = this.getData();

        let add_new_record_button = $("<button></button>");
        add_new_record_button.addClass('btn');
        add_new_record_button.addClass('btn-primary');
        add_new_record_button.attr('data-toggle', 'modal');
        add_new_record_button.attr('data-target', '#add-new-record-modal');
        add_new_record_button.text('Add New Record');
        elements.append(add_new_record_button);

        //TODO: Add button or input here that hides the row-level Actions column

        let table = $("<table></table>");
        this.table_element = table;
        table.addClass('styled-table');

        let record_name = xhr_response['record_definition']['record_name'];

        //console.log('FilterTable::populateFilterTable: xhr_response', xhr_response);

        var retModal = this.generateModal(xhr_response['record_definition']['record_fields'], record_name);

        if(Array.isArray(retModal)) {
            for(let i = 0; i < retModal.length; i++) {
                let modal = retModal[i];
                elements.append(modal.populateModal());
            }
        } else {
            elements.append(retModal.populateModal());
        }

        let h2 = $("<h2></h2>");
        h2.text(record_name);
        elements.append(h2);

        let form = $("<form></form>");
        form.addClass('form-inline');
        elements.append(form);

        let input = $("<input></input>");
        input.addClass('form-control');
        input.addClass('table-search');
        input.addClass('mr-sm-2');
        input.attr('type', 'text');
        input.attr('placeholder', 'Search');
        form.append(input);

        elements.append(table);
        
        table.children('h2').text(record_name);
        table.attr('xhr-table-record-name', record_name);


        let thead = $("<thead></thead>");
        let tbody = this.updateTable(elements, record_name, xhr_response, null);

        table.append(thead);
        table.append(tbody);

        let tr = $("<tr></tr>");
        thead.append(tr);

        for(let i = 0; i < xhr_response['record_definition']['record_fields'].length; i++) {
            if(xhr_response['record_definition']['record_fields'][i]['field_name'] == 'table')
                continue;
            let header = $("<th></th>");
            tr.append(header);
            header.text(xhr_response['record_definition']['record_fields'][i]['field_name']);
        }

        let actionsTh = $("<th></th>");
        actionsTh.text('Actions');
        tr.append(actionsTh);

        register_tables();

        const endTime = Date.now();
        console.log('populateFilterTable run time:', endTime - startTime, 'ms');
    }
}

class RecordDefinitionsFilterTable extends FilterTable {
    constructor(table_name, data, columns) {
        super(table_name, data, columns);
        this.table_name = table_name;
        this.data = data;
        this.columns = columns;
        this.modals = [];
        this.html_element = null;
        this.table_element = null;
        this.new_fields_counter = 0;
    }
    getModal(name) {
        for(let i = 0; i < this.modals.length; i++) {
            if(this.modals[i].getModalId() == name)
                return this.modals[i];
        }
        return null;
    }
    getFieldTypeSelect() {
        const startTime = Date.now();
        let fieldType = $("<select></select>");
        fieldType.addClass('form-control');
        fieldType.attr('name', 'field_type' + this.new_fields_counter);

        let options = ["Text", "Integer", "Boolean", "Date"];
        options.forEach(option => {
            let optElement = $("<option></option>").text(option).val(option);
            fieldType.append(optElement);
        });

        const endTime = Date.now();
        console.log('getFieldTypeSelect run time:', endTime - startTime, 'ms');

        return fieldType;
    }
    getNewFieldElement() {
        const startTime = Date.now();

        let row = $("<div></div>").addClass('form-row');
        
        row.append(this.getFieldTypeSelect());
        
        let fieldName = $("<input></input>");
        fieldName.addClass('form-control');
        fieldName.attr('type', 'text');
        fieldName.attr('name', 'field_name' + this.new_fields_counter);
        row.append(fieldName);

        let deleteButton = $("<button></button>");
        deleteButton.text('Delete');
        deleteButton.addClass('btn');
        deleteButton.addClass('btn-danger');
        deleteButton.on('click', function(event) {
            $(this).parent().remove();
        });
        row.append(deleteButton);

        this.new_fields_counter++;

        const endTime = Date.now();
        console.log('getNewFieldElement run time:', endTime - startTime, 'ms');

        return row;
    }
    generateModal(fields) {
        const startTime = Date.now();
        let filter_table = this;
        let modal = new Modal('add-new-record-definition-modal', 'Add New Record Definition');
        //console.log('RecordDefinitionsFilterTable::generateModal: fields', fields);
        let form = modal.addForm('POST', '/xhr/new-record-definition', fields);

        let row = $("<div></div>").addClass('form-row align-items-center');  

        let label = $("<label></label>").addClass('col-sm-4 col-form-label').text('Record Def. Name'); 
        
        let input = $("<input>").addClass('form-control col-sm-8').attr('type', 'text').attr('name', 'record_def_name');
        
        row.append(label);
        row.append(input);
        
        form.append(row);

        form.append(this.getNewFieldElement());

        let add_field_button = $("<button></button>"); 
        add_field_button.text('Add Field');
        add_field_button.on('click', function(event) {
            form.append(filter_table.getNewFieldElement());
        });
        add_field_button.addClass('btn');
        add_field_button.addClass('btn-primary');
        modal.getModalBody().append(add_field_button);

        modal.addFormOnSubmit(function(event) {
            let form = $(this).parent().parent().children('.modal-body').children('form');
            let alert = $(this).parent().parent().children('.modal-body').children('.alert-success');

    
            let data = form.serialize();
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: data,
                success: function(data) {
                    clear_modal_form(form);
                    alert.show();
                    hide_element_with_delay(alert, 3000);
                    query_xhr("/xhr/record-definition", "GET", {}, function(data) {
                        table_element = filter_table.getTableElement();
                    });
                },
                error: function(data) {
                    console.log(data);
                }
            });
        });

        const endTime = Date.now();
        console.log('generateModal run time:', endTime - startTime, 'ms');
        return modal;
    }
    populateFilterTable() {
        const startTime = Date.now();
        let elements = this.getHtmlElement();
        let xhr_response = this.getData();

        let add_new_record_button = $("<button></button>");
        add_new_record_button.addClass('btn');
        add_new_record_button.addClass('btn-primary');
        add_new_record_button.attr('data-toggle', 'modal');
        add_new_record_button.attr('data-target', '#add-new-record-definition-modal');
        add_new_record_button.text('Add New Record Definition');
        elements.append(add_new_record_button);

        let table = $("<table></table>");
        this.table_element = table;
        table.addClass('styled-table');

        //console.log('RecordDefinitionsFilterTable::populateFilterTable: xhr_response', xhr_response);

        elements.append(this.generateModal(xhr_response['record_definitions']).populateModal());

        let h2 = $("<h2></h2>");
        h2.text('record_name');
        elements.append(h2);

        let form = $("<form></form>");
        form.addClass('form-inline');
        elements.append(form);

        let input = $("<input></input>");
        input.addClass('form-control');
        input.addClass('table-search');
        input.addClass('mr-sm-2');
        input.attr('type', 'text');
        input.attr('placeholder', 'Search');
        form.append(input);

        elements.append(table);
        
        table.children('h2').text('record_name');

        let thead = $("<thead></thead>");
        let tbody = $("<tbody></tbody>");

        table.append(thead);
        table.append(tbody);

        let tr = $("<tr></tr>");
        let th = $("<th></th>");
        th.text('Record Definition');
        thead.append(tr);
        tr.append(th);

        let actionsTh = $("<th></th>");
        actionsTh.text('Actions');
        tr.append(actionsTh);

        for(var i in xhr_response['record_definitions']) {
            //console.log(i);
            let tr = $("<tr></tr>");
            tr.addClass('table-body-row');
            let td = $("<td></td>");
            let a = $("<a></a>");
            a.attr('href', '/admin/show-records?r=' + i);
            a.text(i);
            td.append(a);
            tr.append(td);
            tbody.append(tr);
            
            /*let actionsTd = $("<td></td>");
            actionsTd.attr('filter-table-exclude-search', 'true');
            tr.append(actionsTd);
            let actions = $("<div></div>");
            actionsTd.append(actions);
            let auditBtn = $("<button></button>");
            auditBtn.addClass('btn');
            auditBtn.addClass('btn-primary');
            auditBtn.attr('record-row-id', row_id);
            auditBtn.text('Download');*/
        }

        register_tables();

        const endTime = Date.now();
        console.log('populateFilterTable run time:', endTime - startTime, 'ms');
    }
}

document.addEventListener('DOMContentLoaded', function(){
    $(".filter-table").each(function(index) {
        const startTime = Date.now();
        let table = $(this);
        if($(this).attr('xhr-table') !== undefined) {
            let xhr_table_type = $(this).attr('xhr-table');
            if(xhr_table_type == 'show-records') {
                xhr('/xhr/record', 'GET', {record_name: $(this).attr('xhr-table-record-name')}, function(data) {
                    let xhr_response = JSON.parse(data);
                    let filter_table = new RecordsFilterTable(xhr_response['record_name'], xhr_response, xhr_response['record_definition']['record_fields']);
                    filter_table.setHtmlElement(table);
                    filter_table.populateFilterTable();
                }, function(data) {
                    //console.log(data);
                });
            }
            if(xhr_table_type == 'show-record-definitions') {
                xhr('/xhr/record-definition', 'GET', {}, function(data) {
                    let xhr_response = JSON.parse(data);
                    let filter_table = new RecordDefinitionsFilterTable('record-definitions', xhr_response, ['table_name', 'view_schema']);
                    filter_table.setHtmlElement(table);
                    filter_table.populateFilterTable();
                }, function(data) {
                    //console.log(data);
                });
            }
        }
        const endTime = Date.now();
        console.log('Filter table render time:', endTime - startTime, 'ms');
    });
    
    // Create login modal instance
    const loginModal = new Modal('login-modal', 'Login');
    loginModal.populateModal();
    document.body.appendChild(loginModal.html_element[0]);

    // Add login form
    const loginForm = $('<form id="login-form"></form>');
    
    const usernameGroup = $('<div class="form-group"></div>');
    usernameGroup.append('<label for="username">Username</label>');
    usernameGroup.append('<input type="text" class="form-control" id="username" name="username" required>');
    
    const passwordGroup = $('<div class="form-group"></div>');
    passwordGroup.append('<label for="password">Password</label>');
    passwordGroup.append('<input type="password" class="form-control" id="password" name="password" required>');
    
    loginForm.append(usernameGroup);
    loginForm.append(passwordGroup);
    
    loginModal.getModalBody().append(loginForm);
    
    // Add login and signup buttons to footer
    const loginButton = $('<button type="button" class="btn btn-primary">Login</button>');
    const signupButton = $('<button type="button" class="btn btn-secondary">Sign Up</button>');
    
    loginModal.getModalFooter().append(loginButton);
    loginModal.getModalFooter().append(signupButton);

    // Create signup form (initially hidden)
    const signupForm = $('<form id="signup-form" style="display: none;"></form>');
    
    const emailGroup = $('<div class="form-group"></div>');
    emailGroup.append('<label for="email">Email</label>');
    emailGroup.append('<input type="email" class="form-control" id="email" name="email" required>');
    
    const signupUsernameGroup = $('<div class="form-group"></div>');
    signupUsernameGroup.append('<label for="signup-username">Username</label>');
    signupUsernameGroup.append('<input type="text" class="form-control" id="signup-username" name="username" required>');
    
    const signupPasswordGroup = $('<div class="form-group"></div>');
    signupPasswordGroup.append('<label for="signup-password">Password</label>');
    signupPasswordGroup.append('<input type="password" class="form-control" id="signup-password" name="password" required>');
    
    signupForm.append(emailGroup);
    signupForm.append(signupUsernameGroup);
    signupForm.append(signupPasswordGroup);
    
    loginModal.getModalBody().append(signupForm);

    // Handle switching between login and signup
    signupButton.click(function() {
        if (signupForm.is(':hidden')) {
            loginForm.hide();
            signupForm.show();
            loginModal.getModalHeader().find('.modal-title').text('Sign Up');
            loginButton.text('Back to Login');
            signupButton.text('Create Account');
        } else {
            handleSignup();
        }
    });

    loginButton.click(function() {
        if (signupForm.is(':visible')) {
            signupForm.hide();
            loginForm.show();
            loginModal.getModalHeader().find('.modal-title').text('Login');
            loginButton.text('Login');
            signupButton.text('Sign Up');
        } else {
            handleLogin();
        }
    });

    // Handle login link click
    document.querySelectorAll('#login-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            $(loginModal.html_element).modal('show');
        });
    });

    // Handle form submissions
    async function handleLogin() {
        const formData = new FormData(document.getElementById('login-form'));

        try {
            const response = await fetch('/xhr/login', {
                method: 'POST',
                body: formData,
                credentials: 'include' // Important for cookies
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            //console.log('Login response:', data);
            
            if (data.xhr_response_status === 'success') {
                $(loginModal.html_element).modal('hide');
                //window.location.reload(); // Refresh page to update user state
                $("#login-link").addClass('hidden');
                $("#my-account").removeClass('hidden');

                //TODO: Send signal to update pages & call ajaxes to update state to logged in, without reload
            } else {
                //TODO: Render box that gives error instead of intrusive native alert
                alert(data.error || 'Login failed. Please check your credentials.');
            }
        } catch (error) {
            console.error('Login error:', error);
            //TODO: Render box that gives error instead of intrusive native alert
            alert('An error occurred during login.');
        }
    }

    async function handleSignup() {
        const formData = new FormData(document.getElementById('signup-form'));

        try {
            const response = await fetch('/xhr/signup', {
                method: 'POST',
                body: formData,
                credentials: 'include' // Important for cookies
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            //console.log('Signup response:', data);
            
            if (data.xhr_response_status === 'success') {
                $(loginModal.html_element).modal('hide');
                
                //TODO: Render box that gives success instead of intrusive native alert
                alert('Account created successfully! Please log in.');
                // Switch back to login form
                signupForm.hide();
                loginForm.show();
                loginModal.getModalHeader().find('.modal-title').text('Login');
                loginButton.text('Login');
                signupButton.text('Sign Up');
            } else {
                //TODO: Render box that gives error instead of intrusive native alert
                alert(data.error || 'Signup failed. Please try again.');
            }
        } catch (error) {
            console.error('Signup error:', error);
            //TODO: Render box that gives error instead of intrusive native alert
            alert('An error occurred during signup.');
        }
    }
});


function makeEditable(element) {
    const $element = $(element);

    // Check if an input already exists to avoid duplicates
    if ($element.data('editing')) {
        return;
    }

    const currentText = $element.text();

    const record_id = $element.parent().parent().attr('xhr-record-id');
    const record_name = $element.parent().parent().attr('xhr-record-name');
    const field_type = $element.parent().attr('xhr-record-type');
    const field_name = $element.parent().attr('xhr-field-name');

    // Create an input element
    const $input = $('<input>', {
        type: 'text',
        value: currentText,
        blur: function () {
            // Replace input with the original element when input loses focus
            $element.css('display', 'inline'); // Show the original element
            $element.data('editing', false); // Reset editing state
            $input.remove(); // Remove the input element
            ajaxInlineEditor(record_name, record_id, field_name, field_type, $input.val(), $element);
        },
        keydown: function (event) {
            if (event.key === 'Enter') {
                $input.blur(); // Simulate blur when Enter is pressed
            }
        }
    });

    $input.css({
        'width': $element.outerWidth() + 5, // Make input same width as the original element
        'height': $element.outerHeight() + 2, // Match the height as well
        'padding': $element.css('padding'), // Match the padding
        'font-size': $element.css('font-size'), // Match the font size
        'border': '1px solid #ccc', // Optional: set border if needed
        'box-sizing': 'border-box' // Ensure the width includes padding and border
    });

    // Set data attribute to prevent multiple inputs
    $element.data('editing', true);

    // Hide the original element and insert the input
    $element.css('display', 'none'); // Hide the original element
    $element.after($input); // Add input after the element
    $input.focus(); // Focus on the input
}

function ajaxInlineEditor(record_name, record_id, field_name, field_type, new_value, $element) {
    fields = {
        [field_name]: {
            'value': new_value,
            'type': 'TEXT'
        }
    };

    criteria = {
        'id': {
            'value': record_id,
            'type': 'INT'
        }
    };
    data_payload = {
        record_name: record_name,
        fields: JSON.stringify(fields),
        criteria: JSON.stringify(criteria)
    };
    
    //TODO: Don't send iff no changes
    $.ajax({
        url: '/xhr/update_record',
        method: 'POST',
        data: data_payload,
        success: function(data) {
            //console.log('Update response:', data);
            $element.text(new_value);
        },
        error: function(data) {
            //TODO: Render box that gives error instead of intrusive native alert
            alert('Update error:', data);
        }
    });
}