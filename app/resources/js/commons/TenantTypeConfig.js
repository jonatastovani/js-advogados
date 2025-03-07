export class TenantTypeConfig {

    _objConfigs = {
        domainCustom: {
            nameAttributeKey: undefined,
            headerAttributeKey: undefined,
        },
        selectedValue: undefined,
    };

    _blnDomainCustom = false;

    constructor() {
    }

    set setDomainCustom(domainCustom) {
        this._blnDomainCustom = true;
        this._objConfigs.domainCustom = JSON.parse(JSON.stringify(domainCustom));
    }

    get getStatusBlnCustom() {
        return this._blnDomainCustom;
    }

    set setSelectedValue(selectedValue) {
        this._objConfigs.selectedValue = selectedValue;
    }

    get getSelectedValue() {
        return this._objConfigs.selectedValue;
    }

    get getNameAttributeKey() {
        return this._objConfigs.domainCustom.nameAttributeKey;
    }

    get getHeaderAttributeKey() {
        return this._objConfigs.domainCustom.headerAttributeKey;
    }
}