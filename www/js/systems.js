function StmSystem(t, e) {
    this.id = t,
    this.nom = e,
    this.getId = function() {
        return this.id
    }
    ,
    this.getNom = function() {
        return this.nom
    }
    ,
    this.isEqualByName = function(t) {
        return this.nom == t
    }
    ,
    this.isEqualById = function(t) {
        return this.id == t
    }
}
var stmSystems = {
    chargedSystems: null,
    init: function() {
        if (existStorage("localStorage")) {
            var t = window.localStorage.getItem("stm_ChargedSystems");
            if (null != t) {
                var e = JSON.parse(t);
                this.chargedSystems = new Array;
                for (var s = e.length, r = 0; s > r; r++) {
                    var i = new StmSystem(e[r].id,e[r].nom);
                    this.addSystem(i)
                }
            } else
                this.chargedSystems = new Array
        }
    },
    setExampleData: function() {
        var t = $.Deferred();
        return existStorage("localStorage") && null == window.localStorage.getItem("stm_ChargedSystems"),
        t.resolve(),
        t.promise()
    },
    addSystem: function(t) {
        null == this.chargedSystems && this.init(),
        this.exist(t.getNom()) || this.chargedSystems.push(t)
    },
    exist: function(t) {
        var e = $.grep(this.chargedSystems, function(e) {
            return e.isEqualByName(t)
        });
        return e.length > 0
    },
    getById: function(t) {
        var e = $.grep(this.chargedSystems, function(e) {
            return e.isEqualById(t)
        });
        return e[0]
    },
    save: function() {
        if (existStorage("localStorage")) {
            var t = JSON.stringify(this.chargedSystems);
            window.localStorage.setItem("stm_ChargedSystems", t)
        }
    }
};
