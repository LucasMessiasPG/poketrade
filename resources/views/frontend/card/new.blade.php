<div class="container row">
    <form autocomplete="off" #ff="ngForm" (submit)="searchCard(filter)">
        <div class="col s6 right">
            <button type="button" class="right waves-effect waves-light btn" (click)="backPage()"><i
                        class="material-icons right">list</i> List
            </button>
        </div>
        <div class="col s6 left">
            <h5>Add New Card</h5>
        </div>
        <div class="col s12">
            <hr>
        </div>
        <div class="input-field col s12 m4">
            <select name="set">
                <option value="">All</option>
                <option *ngFor="let set of sets" value="{{ set.id_set }}">{{ set.name }}</option>
            </select>
            <label>Set</label>
        </div>
        <div class="input-field col s12 m6">
            <input autocomplete="off" type="text" id="name" name="name" [(ngModel)]="filter.name">
            <label for="name">Card Name</label>
        </div>
        <div class="input-field col s12 m2">
            <input autocomplete="off" type="text" id="number" name="number" [(ngModel)]="filter.number">
            <label for="number">Card Number</label>
        </div>
        <div class="col s12">
            <button type="submit" class="btn waves-light waves-effect right"> Search Card</button>
        </div>
    </form>
    <div *ngIf="(cardsFilter && cardsFilter.length > 0)">
        <hr class="col s12">
        <div class="col s12 margin-top">
            <table class="striped bordered highlight">
                <thead>
                <tr>
                    <th>Card</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Super Type</th>
                    <th>Set</th>
                    <th><span class="right">Add</span></th>
                </tr>
                </thead>
                <tbody>
                <tr *ngFor="let card of cardsFilter">
                    <td class="div-my-card-img">
                        <div>
                            <img class="very-small-card materialboxed" src="{{ card.image_url }}" alt="">
                        </div>
                    </td>
                    <td><a [routerLink]="['/details',card.id_card]">{{ card.name_card }}</a></td>
                    <td>{{ card.subtype }}</td>
                    <td>{{ card.supertype }}</td>
                    <td>{{ card.set }}</td>
                    <td>
                        <button onclick="setTimeout(function() { document.getElementById('add').scrollIntoView()}, 50);" (click)="selectCardF(card)" type="button" class="right waves-effect waves-light btn"><i
                                    class="material-icons">queue</i></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <hr class="col s12">
    </div>
    <form autocomplete="off" class="margin-top" #f="ngForm" (submit)="addNewCard(new_card)">
        <div *ngIf="selectCard" class="margin-top">
            <div class="col s12 m3 center" id="add">
                <img class="new-card-img" class="img-responsive" src="{{ selectCard.image_url }}" alt="">
            </div>
            <div class="col s12 m9">
                <div class="col s12">
                    <h3 class="center"><strong>{{ selectCard.name_card }}</strong></h3>
                </div>
                <div class="col s12 m3">
                    <p class="left">Set: <strong>{{ selectCard.set }}</strong></p>
                </div>
                <div class="col s12 m3">
                    <p class="left">Rarity: <strong>{{ selectCard.rarity }}</strong></p>
                </div>
                <div class="col s12 m3">
                    <p class="left">Super Type: <strong>{{ selectCard.supertype }}</strong></p>
                </div>
                <div class="col s12 m3">
                    <p class="left">Type: <strong>{{ selectCard.subtype }}</strong></p>
                </div>
                <div *ngIf="selectCard.ability.length > 0" class="col s12">
                    <div class="col s12">
                        <poke-ability [ability]="selectCard.ability"></poke-ability>
                    </div>
                </div>
                <div *ngIf="selectCard.attack.length > 0" class="col 12">
                    <div class="col s12">
                        <poke-attack [attack]="selectCard.attack"></poke-attack>
                    </div>
                </div>
                <div class="col s12" >
                    <div class="input-field col s12 m3">
                        <input type="text" id="amount" name="amount" [(ngModel)]="new_card.amount">
                        <label for="amount">Amount</label>
                    </div>
                    <div class="input-field col s12 m3" *ngIf="optionField">
                        <input type="text" id="price" name="price" [(ngModel)]="new_card.pp">
                        <label for="price">PokePoint</label>
                    </div>
                    <div class="input-field col s12 m3">
                        <div class="switch">
                            <label>
                                Foil
                                <input type="checkbox" [(ngModel)]="new_card.foil" name="foil">
                                <span class="lever"></span>
                            </label>
                        </div>
                        <p></p>
                        <p></p>
                    </div>
                    <div class="input-field col s12 m3">
                        <div class="switch">
                            <label>
                                Reverse Foil
                                <input type="checkbox" [(ngModel)]="new_card.reverse_foil" name="reverse_foil">
                                <span class="lever"></span>
                            </label>
                        </div>
                        <p></p>
                        <p></p>
                    </div>
                    <div class="col s12 m12 margin-top">
                        <button class="btn waves-light waves-effect right"><i class="material-icons">done_all</i></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>