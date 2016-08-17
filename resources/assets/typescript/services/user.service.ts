import {Injectable, EventEmitter} from "@angular/core";
import {Http} from "@angular/http";
import {Router} from "@angular/router";
import {Observable} from "rxjs/Rx";
import {Modal} from "./modal.service";
import {Toast} from "../services/toast.service";

@Injectable()
export class User {
    private tutorial_show = false
    public login$:EventEmitter<any>;
    public cards$:EventEmitter<any>;
    public id_user;
    public login;
    public email;
    public cards;
    private _url = 'http://10.240.4.225:8000/';

    constructor(
        private http:Http,
        private modal:Modal,
        private router:Router,
        private toast: Toast,
    ) {
        this.login$ = new EventEmitter();
        this.cards$ = new EventEmitter();
    }

    public checkLogin() {
        if (this.checkStorage()) {
            return true;
        }
        return false;
    }

    public getCards() {

        if (this.cards) {
            return new Observable(observer => {
                observer.next(this.cards);
            })
        }

        this._url = this._url;
        var url = this._url + 'api/my-cards';
        return this.http.get(url)
            .map(res => {
                var response = res.json();
                if (this.checkResponse(response, url)) {
                    this.cards = response.data;
                    return response.data;
                }
                if(response.msg)
                    this.toast.show(response.msg)
                throw 'Erro';
            })
    }

    private checkResponse(response, url) {
        if (!response.status || response.status && response.status == 'error')
            throw 'Error response ' + url;

        if (response.status == 'success')
            return true;
    }

    public makeLogin(user) {
        if (this.checkStorage())
            return this.emitLogin();

        this.http.post(this._url + 'login-user', user)
            .subscribe(res => {
                var response = res.json();
                if (response.user) {
                    this.id_user = response.user.id_user;
                    this.email = response.user.email;
                    this.login = response.user.login;
                    localStorage.setItem('user', JSON.stringify(response));
                    this.router.navigateByUrl('/home')
                    location.reload();
                }else {
                    if (response.warning)
                        this.toast.show(response.warning)
                }

            })
    }

    public emitLogin() {
        return this.login$.emit({email: this.email, login: this.login, cache: 2})
    }

    private checkStorage() {
        if (localStorage.getItem('user')) {
            var local_user = JSON.parse(localStorage.getItem('user'));

            if(local_user.tutorial && this.tutorial_show == false) {
                this.modal.init()
                this.modal.show('#tutorial')
                this.tutorial_show = true;
            }
            if (local_user.user.login && local_user.user.email) {
                this.id_user = local_user.user.id_user;
                this.email = local_user.user.email;
                this.login = local_user.user.login;
                return true;
            }
        }
        return false;
    }

    public register(user) {
        return this.http.post(this._url + 'register-user', user)
            .map(res => {
                var response = res.json();
                if (response.user) {
                    this.id_user = response.user.id_user;
                    this.email = response.user.email;
                    this.login = response.user.login;
                    localStorage.setItem('user', JSON.stringify(response));
                    return true;
                }
                return false;

            })
    }

    public tutorial(b:boolean) {
        if(b)
            this.http.get(this._url + 'user/tutorial/1').subscribe(res => {})
        else
            this.http.get(this._url + 'user/tutorial/0').subscribe(res => {})

        if(localStorage.getItem('user')) {
            var temp_user = JSON.parse(localStorage.getItem('user'));

            if(temp_user.tutorial)
                temp_user.tutorial = !b;

            localStorage.setItem('user',JSON.stringify(temp_user));
        }
    }

    public getProfile(id_user) {
        return this.http.get(this._url + 'user/profile/'+id_user)
            .map(res => {
                var response = res.json();
                if(response.user){
                    return response.user
                }else{
                    if(response.warning)
                        this.toast.show(response.warning)
                }
            })
    }
}