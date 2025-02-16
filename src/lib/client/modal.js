

class Modal {
    constructor(modal_id, modal_name) {
        this.modal_id = modal_id;
        this.modal_name = modal_name;
        this.modal_header = null;
        this.modal_body = null;
        this.modal_footer = null;
        this.html_element = $("<div></div>");
        this.populated = false;
    }
    getModalId() {
        return this.modal_id;
    }
    getModalName() {
        return this.modal_name;
    }
    getModalHeader() {
        if(this.modal_header == null)
            this.populateModal();
        return this.modal_header;
    }
    getModalBody() {
        if(this.modal_body == null)
            this.populateModal();
        return this.modal_body;
    }
    getModalFooter() {
        if(this.modal_footer == null)
            this.populateModal();
        return this.modal_footer;
    }
    populateModal() {
        if(this.populated)
            return this.html_element;
        this.populated = true;
        this.html_element.addClass('modal');
        this.html_element.addClass('fade');
        this.html_element.attr('id', this.modal_id);

        let modal_dialog = $("<div></div>");
        modal_dialog.addClass('modal-dialog');
        this.html_element.append(modal_dialog);
        this.modal_dialog = modal_dialog;

        let modal_content = $("<div></div>");
        modal_content.addClass('modal-content');
        modal_dialog.append(modal_content);
        this.modal_content = modal_content;

        this.modal_header = $("<div></div>");
        this.modal_header.addClass('modal-header');
        modal_content.append(this.modal_header);

        let h5 = $("<h5></h5>");
        h5.addClass('modal-title');
        h5.text(this.modal_name);
        this.modal_header.append(h5);

        let button = $("<button></button>");
        button.addClass('close');
        button.attr('data-dismiss', 'modal');
        button.html('&times;');
        this.modal_header.append(button);

        this.modal_body = $("<div></div>");
        this.modal_body.addClass('modal-body');
        modal_content.append(this.modal_body);

        this.modal_footer = $("<div></div>");
        this.modal_footer.addClass('modal-footer');
        modal_content.append(this.modal_footer);

        return this.html_element;
    }
    addBodyElement(element) {
        this.getModalBody().append(element);
        return this;
    }
    addForm(method, action, fields) {
        let form = $("<form></form>");
        form.attr('method', method);
        form.attr('action', action);
        this.getModalBody().append(form);

        for(let i = 0; i < fields.length; i++) {
            let field = fields[i];
            if(field['field_name'] == 'id')
                continue;
            let div = $("<div></div>");
            div.addClass('form-group');
            let label = $("<label></label>");
            label.attr('for', field['field_name']);
            label.text(field['field_name']);
            div.append(label);
            let input = $("<input></input>");
            input.addClass('form-control');
            input.attr('type', 'text');
            input.attr('name', field['field_name']);
            if(field['hidden'] !== undefined && (field['hidden'] == true || field['hidden'] == 'true'))
                input.hide();
            if(field['field_name'] == 'table') {
                input.val(field['field_value']);
                input.hide();
                label.hide();
            }
            div.append(input);
            form.append(div);

            if(field['field_type'] == '3') {
                input.addClass('pop-datepicker');
                input.datepicker({
                    dateFormat: 'yy-mm-dd'
                });
            }
        }

        let submit = $("<button></button>");
        submit.addClass('btn');
        submit.addClass('btn-primary');
        submit.attr('type', 'submit');
        submit.text('Submit');
        this.modal_footer.append(submit);
        
        return form;
    }
    addFormOnSubmit(callback) {
        //console.log('Modal::addFormOnSubmit : callback', callback);
        this.modal_footer.children('button').on('click', callback);
    }
    resizeLarge() {
        this.populateModal();
        this.html_element.children('.modal-dialog').addClass('modal-lg');
        return this;
    }
}

class ModalFactory {
    constructor() {
        this.modals = [];
    }
    
    addModal(modal) {
        this.modals.push(modal);
        return modal;
    }

    getModal(modal_name) {
        return this.modals.find(modal => modal.modal_name === modal_name);
    }
}

var modal_factory = undefined;

function get_modal_factory() {
    if(modal_factory === undefined) {
        modal_factory = new ModalFactory();
    }
    return modal_factory;
}
