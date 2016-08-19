import { Routes, RouterModule } from '@angular/router';
import {HomeComponent} from "./Components/home.component";
import {SearchComponent} from "./Components/search.component";
import {ListComponent} from "./Components/list.component";
import {TradesComponent} from "./Components/trades.component";
import {LoginComponent} from "./Components/login.component";
import {MyCardsComponents} from "./Components/my-cards.component";
import {ProfileComponent} from "./Components/profile.component";
import {NewCardComponent} from "./Components/card/new-card.component";
import {HistoricComponent} from "./Components/historic.component";
import {DatailsComponent} from "./Components/card/datails.component";

const routes: Routes = [
    {path: '',redirectTo: '/home',pathMatch: 'full'},
    {path: 'home',component: HomeComponent},
    {path: 'perfil',component: HomeComponent},
    {path: 'search',component: SearchComponent},
    {path: 'list',component: ListComponent},
    {path: 'trades',component: TradesComponent},
    {path: 'login',component: LoginComponent},
    {path: 'historic',component: HistoricComponent},
    {path: 'my-cards',component: MyCardsComponents},
    {path: 'my-cards/new',component: NewCardComponent},
    {path: 'profile/:id',component: ProfileComponent},
    {path: 'datails/:id',component: DatailsComponent},
];

export const routing = RouterModule.forRoot(routes);
