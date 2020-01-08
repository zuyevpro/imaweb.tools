function getNewMigrationWindow() {
	dialog = new BX.CDialog({
		title: "Новая миграция",
		head: "Укажите код миграции (символы, латинницей, символы дефиса и подчёркивания)",
		content: '<form name="" id="form_migration_new"><input type="text" name="name" value="" style="width: 320px;" /></form>',
		width: 600,
		height: 200,
		buttons: [
			{
				title: "Сохранить",
				name: "save",
				id: "new_migration",
				className: "adm-btn-save",
				action: function () {
					var migrationName = $(dialog.DIV).find('input').val(),
						self = this;
					BX.showWait(this.parentWindow.DIV);
					$.ajax({
						url: location.pathname,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'new',
							name: migrationName,
						},
						success: function (json) {
							BX.closeWait(self.parentWindow.DIV);
							if (json.status) {
								location.reload();
							}
							else {
								alert(json.message);
							}
						},
						fail: function() {
							BX.closeWait(self.parentWindow.DIV);
						}
					});
				},
				onclick: "BX.WindowManager.Get().Close()"
			}
		]

	});

	dialog.Show();
}

function runMigrations() {
	var holder = BX('table_migrations_list_result_div');
	BX.showWait(holder);
	$.ajax({
		url: location.pathname,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'run'
		},
		success: function (json) {
			BX.closeWait(holder);
			if (json.status) {
				location.reload();
			}
			else {
				alert(json.message);
			}
		},
		fail: function() {
			BX.closeWait(holder);
		}
	});
}

function rollbackMigrations() {
	var holder = BX('table_migrations_list_result_div');
	BX.showWait(holder);
	$.ajax({
		url: location.pathname,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'run',
			rollback: 1
		},
		success: function (json) {
			BX.closeWait(holder);
			if (json.status) {
				location.reload();
			}
			else {
				alert(json.message);
			}
		},
		fail: function() {
			BX.closeWait(holder);
		}
	});
}

function clearAppliedMigrations() {
	if (confirm("Внимание! Будет удалён программный код без возможности восстановления в рамках панели администрирования. Продолжить?")) {
		var holder = BX('table_migrations_list_result_div');
		BX.showWait(holder);
		$.ajax({
			url: location.pathname,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'clear'
			},
			success: function (json) {
				BX.closeWait(holder);
				if (json.status) {
					location.reload();
				}
				else {
					alert(json.message);
				}
			},
			fail: function() {
				BX.closeWait(holder);
			}
		});
	}

}