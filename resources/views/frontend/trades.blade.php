<div class="container">
    <div class="margin-top">
        <div class="row">
            <div class="col s12 m2"><h4>Filter</h4></div>
            <div class="input-field col s12 m3">
                <input autocomplete="off" type="text" id="name" name="name" [(ngModel)]="filtro.name">
                <label for="name" class="active">Name</label>
            </div>
            <div class="input-field col s12 m2">
                <input autocomplete="off" type="text" id="number" name="number" [(ngModel)]="filtro.number">
                <label for="number" class="active">Number</label>
            </div>
            <div class="input-field col s12 m2 switch margin-bot-40">
                <label class="center-align">
                    All
                    <input type="checkbox" name="have" [(ngModel)]="filtro.have">
                    <span class="lever"></span>
                    Have
                </label>
            </div>
            <div class="input-field col s12 m3 right-align">
                <button (click)="clearFilter()" class="btn waves-effect waves-light">Clear</button>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col s12">
            <div class="col s12 m4" *ngFor="let single_card of list_want">
                <poke-card [card]="single_card.card" [want]="single_card"></poke-card>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Card</th>
                    <th class="hide-on-small-and-down">Name / Number</th>
                    <th>PokePoint</th>
                    <th class="center">Trainer</th>
                    <th class="hide-on-small-and-down">Foil</th>
                    <th class="hide-on-small-and-down">Reverse Foil</th>
                    <th class="right">Option</th>
                </tr>
            </thead>
            <tbody>
                <tr *ngFor="let want of list_want | filter:{have:filtro.have,name:filtro.name,number:filtro.number} | paginate: { itemsPerPage: 10, currentPage: p }">
                    <td class="div-my-card-img">
                        <div>
                            <img class="very-small-card materialboxed" src="{{ want.card.image_url }}" alt="{{ want.card.name }}">
                        </div>
                    </td>
                    <td class="hide-on-small-and-down"><a [routerLink]="['/details',want.card.id_card]">{{ want.card.name_card }}</a></td>
                    <td class="right-align"><i class="fa fa-rub"></i> {{ want.pp | PokePoint }}</td>
                    <td class="center"><a [routerLink]="['/profile',want.user.id_user]">{{ want.user.login }}</a></td>
                    <td class="hide-on-small-and-down">{{ want.foil }}</td>
                    <td class="hide-on-small-and-down">{{ want.reverse_foil }}</td>
                    <td class="right-align">
                        <a *ngIf="(user.id_user != want.user.id_user && want.have)" (click)="sendCard(want)" class="btn waves-effect waves-light hide-on-small-and-down">Send this card</a>
                        <a *ngIf="(user.id_user != want.user.id_user && want.have)" (click)="sendCard(want)" class="btn waves-effect waves-light btn-small hide-on-med-and-up"><i class="fa fa-share-square-o"></i></a>
                        <a *ngIf="(user.id_user != want.user.id_user && !want.have)" class="btn waves-effect waves-light disabled tooltipped hide-on-small-and-down" data-position="left" data-delay="50"  data-tooltip="You don't have this card">Send this card</a>
                        <a *ngIf="(user.id_user != want.user.id_user && !want.have)" class="btn waves-effect waves-light disabled tooltipped btn-small hide-on-med-and-up" data-position="left" data-delay="50"  data-tooltip="You don't have this card"><i class="fa fa-share-square-o"></i></a>
                    </td>
                </tr>
            </tbody>
        </table>
        <pagination-controls #pagination1 autoHide="true" (pageChange)="p = $event">
            <poke-paginate [pagination]="pagination1"></poke-paginate>
        </pagination-controls>
    </div>
</div>