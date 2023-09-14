define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'sys/ip_report/index' + location.search,
                    add_url: 'sys/ip_report/add',
                    edit_url: 'sys/ip_report/edit',
                    del_url: 'sys/ip_report/del',
                    multi_url: 'sys/ip_report/multi',
                    import_url: 'sys/ip_report/import',
                    table: 'ip_report',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'type', title: __('Type'), searchList: { "0": __('Type 0'), "1": __('Type 1'), "2": __('Type 2') }, formatter: Table.api.formatter.normal },
                        { field: 'content', title: __('Content'), operate: 'LIKE' },
                        { field: 'num', title: __('Num'), sortable: true },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
